<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Serialization;

use PhoneBurner\SaltLite\Framework\Domain\Memory\Bytes;
use PhoneBurner\SaltLite\Framework\Util\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Serialization\Exception\SerializationFailure;

/**
 * Note: Production should always use the igbinary serializer, as it is faster and
 * significantly more compact than PHP's built-in serializer; however, we support
 * deserializing values serialized with either serializer for backwards compatibility.
 * This also lets us get a small performance boost by hardcoding common values,
 * like 0 and null. We always return these in their PHP serialized format, as it
 * is a few bytes smaller than the igbinary format which has a few bytes of overhead
 * for the header.
 */
class Marshaller
{
    /**
     * The threshold at which a value is considered large enough to be compressed.
     * This should be smaller than the network MTU, accounting for the overhead
     * added by base64 encoding and the Redis protocol. (Assuming that the MTU is
     * between 1300 and 1500 bytes.)
     */
    public const int COMPRESSION_THRESHOLD_BYTES = 1200;

    /**
     * The compression level passed to gzcompress when compressing values is
     * intentionally set to 1 to reduce the CPU overhead of compression, and
     * favor speed over compression ratio, as we can assume that cache values
     * will not exceed a few megabytes in size uncompressed.
     */
    public const int COMPRESSION_LEVEL = 1;

    public const array ZLIB_HEADERS = [
        "\x78\x01",
        "\x78\x5E",
        "\x78\x9C",
        "\x78\xDA",
    ];

    public const string IGBINARY_HEADER = "\x00\x00\x00\x02";

    private const array SERIALIZED_VALUE_MAP = [
        'N;' => null,
        'b:0;' => false,
        'b:1;' => true,
        'a:0:{}' => [],
        'i:0;' => 0,
        'i:1;' => 1,
        'd:0;' => 0.0,
        'd:-0;' => -0.0,
        's:0:"";' => "",
        's:1:"0";' => "0",
        's:1:"1";' => "1",
        "\x00\x00\x00\x02\x00" => null,
        "\x00\x00\x00\x02\x04" => false,
        "\x00\x00\x00\x02\x05" => true,
        "\x00\x00\x00\x02\x14\x00" => [],
        "\x00\x00\x00\x02\x06\x00" => 0,
        "\x00\x00\x00\x02\x06\x01" => 1,
        "\x00\x00\x00\x02\x0c\x00\x00\x00\x00\x00\x00\x00\x00" => 0.0,
        "\x00\x00\x00\x02\x0c\x80\x00\x00\x00\x00\x00\x00\x00" => -0.0,
        "\x00\x00\x00\x02\x0d" => "",
        "\x00\x00\x00\x02\x11\x010" => "0",
        "\x00\x00\x00\x02\x11\x011" => "1",
    ];
    const string BASE64_REGEX = '/^(?:[A-Za-z0-9+\/]|[A-Za-z0-9-_])+={0,2}$/';

    public static function serialize(
        mixed $value,
        Encoding|null $encoding = null,
        bool $use_encoding_prefix = false,
        bool $use_compression = false,
        Bytes $compression_threshold_bytes = new Bytes(self::COMPRESSION_THRESHOLD_BYTES),
        Serializer $serializer = Serializer::Igbinary,
    ): string {
        \is_resource($value) && throw new SerializationFailure('cannot serialize resource');

        $value = match ($value) {
            null => "N;",
            false => "b:0;",
            [] => 'a:0:{}',
            0 => "i:0;",
            0.0 => "d:0;", // also covers -0.0 case, as -0.0 === 0.0
            "" => 's:0:"";',
            "0" => 's:1:"0";',
            true => "b:1;",
            1 => "i:1;",
            "1" => 's:1:"1";',
            default => match ($serializer) {
                Serializer::Igbinary => \igbinary_serialize($value),
                Serializer::Php => \serialize($value),
            } ?: throw new SerializationFailure(
                'failed to serialize value of type ' . \get_debug_type($value),
            ),
        };

        if ($use_compression && \strlen($value) >= $compression_threshold_bytes->value) {
            $value = @\gzcompress($value, self::COMPRESSION_LEVEL) ?: throw new SerializationFailure(
                'failed to compress serialized value with gzcompress',
            );
        }

        return match (true) {
            $encoding === null => $value,
            $use_encoding_prefix => $encoding->prefix() . $encoding->encode($value),
            default => $encoding->encode($value),
        };
    }

    /**
     * @param Encoding|null $encoding The kind of encoding used to serialize the value,
     * if known. Otherwise, we'll check to see if the string looks like it is base64
     * encoded (with any of the four base64 variants) and try to decode it accordingly.
     */
    public static function deserialize(
        string $value,
        Encoding|null $encoding = null,
    ): mixed {
        // Performance shortcut for common values that we always serialize in the PHP format
        // note that we can't use ?? because null is a valid value in the map
        if (\strlen($value) < 13 && \array_key_exists($value, self::SERIALIZED_VALUE_MAP)) {
            return self::SERIALIZED_VALUE_MAP[$value];
        }

        $value = self::decode($encoding, $value);
        $value = self::decompress($value);

        // we can safely use ?: here to check for failures when deserializing because we
        // account for all falsey values by first checking the value map. This includes
        // PHP's serialized null value which lacks the ":" character at the 2nd position
        return match (true) {
            \strlen($value) <= 13 && \array_key_exists($value, self::SERIALIZED_VALUE_MAP) => self::SERIALIZED_VALUE_MAP[$value],
            \str_starts_with($value, self::IGBINARY_HEADER) => \igbinary_unserialize($value) ?: throw new SerializationFailure(
                'igbinary serializer: invalid string',
            ),
            $value[1] === ':' => \unserialize($value, ['allowed_classes' => true]) ?: throw new SerializationFailure(
                'php serializer: invalid string',
            ),
            default => throw new SerializationFailure('unsupported serialization format'),
        };
    }

    /**
     * Try to decode a string that may be base64 encoded, without needing to know
     * which of the four base64 variants was used to encode it. If passed an encoding,
     * we'll just use that. If the string looks like it is base64 encoded, we'll try
     * to decode it. On failure, we just return the original value, assuming it was
     * not actually base64 encoded. We can let the decompress and unserialize steps
     * catch failures if the value is actually bad data.
     */
    private static function decode(Encoding|null $encoding, string $value): string
    {
        try {
            return match (true) {
                $encoding !== null => $encoding->decode($value),
                \str_starts_with($value, Encoding::HEX_PREFIX) => Encoding::Hex->decode(
                    \substr($value, \strlen(Encoding::HEX_PREFIX)),
                ),
                \str_starts_with($value, Encoding::BASE64_PREFIX) => Encoding::decodeBase64(
                    \substr($value, \strlen(Encoding::BASE64_PREFIX)),
                ),
                \str_starts_with($value, Encoding::BASE64URL_PREFIX) => Encoding::decodeBase64(
                    \substr($value, \strlen(Encoding::BASE64URL_PREFIX)),
                ),
                \preg_match(self::BASE64_REGEX, $value) => Encoding::decodeBase64($value),
                default => $value,
            } ?? $value;
        } catch (\Throwable) {
            return $value;
        }
    }

    private static function decompress(string $value): string
    {
        if (\in_array(\substr($value, 0, 2), self::ZLIB_HEADERS, true)) {
            $value = @\gzuncompress($value);
            if ($value === false) {
                throw new SerializationFailure('invalid zlib string');
            }
        }

        return $value;
    }
}
