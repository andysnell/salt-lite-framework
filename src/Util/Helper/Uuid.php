<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

/**
 * Helper class for generating RFC 4122 compliant UUID values.
 * We need to create and use factory instances, as opposed to the vendor `UUID`
 * helper class, because the helper uses a static (i.e. global) factory.
 */
abstract class Uuid
{
    public const string HEX_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    private const string NIL = '00000000-0000-0000-0000-000000000000';

    /**
     * Create an RFC 4122 Version 4 (Random) UUID instance.
     *
     * @link https://uuid.ramsey.dev/en/latest/rfc4122/version4.html
     */
    public static function random(): UuidInterface
    {
        return self::factory()->uuid4();
    }

    /**
     * Create a UUID based on the "Timestamp First COMB" derivative of
     * the RFC 4122 Version 4 (Random) UUID. These UUIDs replace the first 48
     * bits with the microsecond timestamp, retaining 6 bits for the version/variant,
     * and 74 bits of randomness. This produces UUIDs that are monotonically
     * increasing and lexicographically sortable in both hex and byte formats.
     *
     * @link https://uuid.ramsey.dev/en/latest/customize/timestamp-first-comb-codec.html
     */
    public static function ordered(): UuidInterface
    {
        return self::factory()->uuid7();
    }

    /**
     * Create the RFC 4122 Nil Uuid, where all 128-bits are set to 0.
     *
     * @link https://tools.ietf.org/html/rfc4122#section-4.1.7
     */
    public static function nil(): UuidInterface
    {
        static $nil_uuid = self::factory()->fromString(self::NIL);
        return $nil_uuid;
    }

    /**
     * Sometimes we may be working with a value that could either be a hex-string or
     * and instance of `UuidInterface`. This method lets us cleanly cast input
     * to a `UuidInterface` instance.
     */
    public static function instance(UuidInterface|\Stringable|string $uuid): UuidInterface
    {
        return $uuid instanceof UuidInterface ? $uuid : self::factory()->fromString((string)$uuid);
    }

    public static function factory(): UuidFactory
    {
        static $factory = new UuidFactory();
        return $factory;
    }
}
