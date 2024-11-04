<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Marshaller;

use PhoneBurner\SaltLite\Framework\App\PhpRuntimeConfig;
use PhoneBurner\SaltLite\Framework\Cache\Exception\CacheMarshallingError;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

#[Internal]
class RemoteCacheMarshaller implements MarshallerInterface
{
    /**
     * The threshold at which a value is considered large enough to be compressed.
     * This should be smaller than the network MTU, accounting for the overhead
     * added by base64 encoding and the Redis protocol. (Assuming that the MTU is
     * between 1300 and 1500 bytes.)
     */
    public const int COMPRESSION_THRESHOLD_BYTES = 1200;

    /**
     * The compression level passed to `gzcompress` when compressing values is
     * intentionally set to 1 to reduce the CPU overhead of compression, and
     * favor speed over compression ratio, as we can assume that cache values
     * will not exceed a few megabytes in size uncompressed.
     */
    public const int COMPRESSION_LEVEL = 1;

    public const string UNSERIALIZE_CALLBACK_INI_NAME = 'unserialize_callback_func';

    public const string UNSERIALIZE_CALLBACK_INI_VALUE = UnserializeUndefinedClassHandler::class . '::fail';

    public const array ZLIB_HEADERS = [
        "\x78\x01",
        "\x78\x5E",
        "\x78\x9C",
        "\x78\xDA",
    ];

    public const string IGBINARY_HEADER = "\x00\x00\x00\x02";

    private const array PHP_SERIALIZED_VALUE_MAP = [
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
    ];

    private const array IGBINARY_SERIALIZED_VALUE_MAP = [
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

    private readonly \Closure $error_handler;

    public function __construct(
        private readonly Serializer $serializer = Serializer::Igbinary,
        private readonly bool $compress = true,
        private readonly bool $encode = false,
        private readonly bool $throw_on_serialization_failure = false,
        private readonly PhpRuntimeConfig $runtime_config = new PhpRuntimeConfig(),
        private readonly LoggerInterface|null $logger = null,
    ) {
        $this->error_handler = static function (int $level, string $message, string $file = '', int $line = 0): never {
            throw new \ErrorException($message, 0, $level, $file, $line);
        };
    }

    /**
     * Serializes a list of values.
     *
     * When serialization fails for a specific value, no exception should be
     * thrown. Instead, its key should be listed in $failed.
     *
     * We have to set a custom error handler here in order to catch the serialization
     * of resources, which would otherwise throw a warning and not return false.
     *
     * @phpstan-ignore parameterByRef.unusedType (Must comply with vendor interface)
     */
    #[\Override]
    public function marshall(array $values, array|null &$failed = null): array
    {
        $serialized = [];
        $failed ??= [];

        $previous_error_handler = \set_error_handler($this->error_handler);

        try {
            foreach ($values as $key => $value) {
                try {
                    $serialized[$key] = $this->serialize($value);
                } catch (\Exception $e) {
                    $this->logger?->error($e->getMessage(), [
                        'key' => $key,
                        'type' => \get_debug_type($value),
                        'exception' => $e,
                    ]);
                    $this->throw_on_serialization_failure
                        ? throw new CacheMarshallingError('Failed to serialize value of type ' . \get_debug_type($value), previous: $e)
                        : $failed[] = $key;
                }
            }
        } finally {
            \set_error_handler($previous_error_handler);
        }

        return $serialized;
    }

    private function serialize(mixed $value): string
    {
        \assert(\extension_loaded('igbinary'));

        $value = match ($value) {
            null => "N;",
            false => "b:0;",
            true => "b:1;",
            [] => 'a:0:{}',
            0 => "i:0;",
            1 => "i:1;",
            0.0 => "d:0;", // also covers -0.0 case, as -0.0 === 0.0
            "" => 's:0:"";',
            "0" => 's:1:"0";',
            "1" => 's:1:"1";',
            default => match ($this->serializer) {
                Serializer::Igbinary => \igbinary_serialize($value) ?? throw new \InvalidArgumentException(
                    'igbinary_serialize failed to serialize value of type ' . \get_debug_type($value),
                ),
                Serializer::Php => ! \is_resource($value) ? \serialize($value) : throw new \InvalidArgumentException(
                    'Cannot serialize values of type resource',
                ),
            },
        };

        if ($this->compress && \strlen($value) > self::COMPRESSION_THRESHOLD_BYTES) {
            $value = \gzcompress($value, self::COMPRESSION_LEVEL) ?: throw new \UnexpectedValueException('compress');
        }

        if ($this->encode) {
            return 'base64:' . \base64_encode($value);
        }

        return $value;
    }

    /**
     * Unmarshalls values originally serialized with either the `serialize` or
     * `igbinary_serialize` functions into their original form, handling input
     * that may be base64 encoded and/or compressed with the gzcompress() function.
     *
     * We have to set a custom unserialize callback here in order to catch the
     * deserialization of undefined classes, which would otherwise be silently
     * deserialized as __PHP_Incomplete_Class instances.
     */
    #[\Override]
    public function unmarshall(string $value): mixed
    {
        // Performance shortcut for common values
        if (\array_key_exists($value, self::PHP_SERIALIZED_VALUE_MAP)) {
            return self::PHP_SERIALIZED_VALUE_MAP[$value];
        }

        $previous_callback = (string)$this->runtime_config->set(
            self::UNSERIALIZE_CALLBACK_INI_NAME,
            self::UNSERIALIZE_CALLBACK_INI_VALUE,
        );

        try {
            return $this->deserialize($value);
        } catch (\Exception $e) {
            $this->logger?->error('Failed to deserialize value', [
                'value' => Str::truncate($value, 1024),
                'exception' => $e,
            ]);
            throw new CacheMarshallingError('Failed to deserialize value: ' . Str::truncate($value), previous: $e);
        } finally {
            $this->runtime_config->set(self::UNSERIALIZE_CALLBACK_INI_NAME, $previous_callback);
        }
    }

    private function deserialize(string $value): mixed
    {
        \assert(\extension_loaded('igbinary'));

        if (\str_starts_with($value, 'base64:')) {
            $value = \base64_decode(\substr($value, 7), true) ?: throw new \UnexpectedValueException('invalid base64 string');
        }

        if (\in_array(\substr($value, 0, 2), self::ZLIB_HEADERS, true)) {
            $value = \gzuncompress($value) ?: throw new \UnexpectedValueException('invalid zlib string');
        }

        // Check the map of known serialized values first, ensuring that any
        // non-truthy values by the map are not accidentally treated as failures.
        return match (true) {
            \array_key_exists($value, self::IGBINARY_SERIALIZED_VALUE_MAP) => self::IGBINARY_SERIALIZED_VALUE_MAP[$value],
            \str_starts_with($value, self::IGBINARY_HEADER) => \igbinary_unserialize($value) ?: throw new \UnexpectedValueException(
                'invalid igbinary serialized string',
            ),
            \array_key_exists($value, self::PHP_SERIALIZED_VALUE_MAP) => self::PHP_SERIALIZED_VALUE_MAP[$value],
            \strpos($value, ':') === 1 => \unserialize($value, ['allowed_classes' => true]) ?: throw new \UnexpectedValueException(
                'invalid php serialized string',
            ),
            default => throw new \DomainException('unsupported serialization format'),
        };
    }
}
