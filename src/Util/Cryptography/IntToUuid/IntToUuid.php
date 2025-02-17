<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\IntToUuid;

use InvalidArgumentException;
use LogicException;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\PackFormat;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

/**
 * Bidirectionally encodes a 64-bit unsigned integer into a valid RFC 4122 UUID,
 * version 4, within an 32-bit namespace, generated with a 32-bit seed value that
 * also functions as a validation checksum when attempting to decode a UUID back
 * into an integer ID.
 */
abstract readonly class IntToUuid
{
    public const string VALIDATION_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[ab89][0-9a-f]{3}-[0-9a-f]{12}$/';

    public const int RFC4122_VERSION = Uuid::UUID_TYPE_RANDOM;

    public const int RFC4122_VARIANT = Uuid::RFC_4122;

    public static function encode(IntegerId $integer_id): UuidInterface
    {
        $id = \pack(PackFormat::INT64_UNSIGNED_BE, $integer_id->value);
        $namespace = \pack(PackFormat::INT32_UNSIGNED_BE, $integer_id->namespace);
        $seed = self::seed($id, $namespace);

        $id ^= \sodium_crypto_generichash($namespace . $seed);
        $namespace ^= \sodium_crypto_generichash($seed);

        return Uuid::fromBytes($namespace . \substr($id, 0, 2) . $seed . \substr($id, 2));
    }

    public static function decode(UuidInterface $uuid): IntegerId
    {
        if (! \preg_match(self::VALIDATION_REGEX, $uuid->toString())) {
            throw new InvalidArgumentException('UUID Does Not Match Required RFC 4122 v4 Format');
        }

        $bytes = $uuid->getBytes();

        $seed = \substr($bytes, 6, 4);
        $namespace = \substr($bytes, 0, 4);
        $id = \substr($bytes, 4, 2) . \substr($bytes, 10);

        $namespace ^= \sodium_crypto_generichash($seed);
        $id ^= \sodium_crypto_generichash($namespace . $seed);
        if (self::seed($id, $namespace) !== $seed) {
            throw new RuntimeException('UUID Could Not Be Decoded Successfully');
        }

        return IntegerId::make(self::unpackInt64($id), self::unpackInt32($namespace));
    }

    private static function seed(string $packed_id, string $packed_namespace): string
    {
        $hash = \sodium_crypto_generichash($packed_id . $packed_namespace);
        $seed = self::unpackInt32(\substr($hash, 0, 4));
        return \pack(PackFormat::INT32_UNSIGNED_BE, $seed & 0x0FFF3FFF | 0x40008000);
    }

    private static function unpackInt32(string $string): int
    {
        $data = (array)\unpack(PackFormat::INT32_UNSIGNED_BE, $string);
        return (int)($data[1] ?? throw new LogicException('UUID Unpack Error'));
    }

    private static function unpackInt64(string $string): int
    {
        $data = (array)\unpack(PackFormat::INT64_UNSIGNED_BE, $string);
        return (int)($data[1] ?? throw new LogicException('UUID Unpack Error'));
    }
}
