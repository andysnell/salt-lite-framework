<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
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
     * We set the generator to the default generator in order to set a private
     * flag in the vendor factory class that will return a more strict interface
     *
     * @link https://uuid.ramsey.dev/en/latest/rfc4122/version4.html
     */
    public static function random(): UuidInterface
    {
        return self::getRandomFactory()->uuid4();
    }

    /**
     * We set the generator to the default generator in order to flip a private
     * flag in the vendor factory class that will return the more strict RFC4122
     * interface instead of a generic `LazyUuidFromString` instance.
     */
    public static function getRandomFactory(): UuidFactoryInterface
    {
        static $random_factory;
        return $random_factory ??= (static function (): UuidFactoryInterface {
            $factory = new UuidFactory();
            $factory->setRandomGenerator($factory->getRandomGenerator());

            return $factory;
        })();
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
        static $previous;
        $previous ??= self::nil();

        do {
            $uuid = self::getOrderedFactory()->uuid4();
        } while ($uuid->getBytes() <= $previous->getBytes());

        $previous = $uuid;

        return UuidV4::fromString($uuid->toString());
    }

    public static function getOrderedFactory(): UuidFactoryInterface
    {
        static $ordered_factory;
        return $ordered_factory ??= (static function (): UuidFactoryInterface {
            $factory = new UuidFactory();
            $factory->setCodec(new TimestampFirstCombCodec($factory->getUuidBuilder()));
            $factory->setRandomGenerator(new CombGenerator(
                $factory->getRandomGenerator(),
                $factory->getNumberConverter(),
            ));

            return $factory;
        })();
    }

    /**
     * Create the RFC 4122 Nil Uuid, where all 128-bits are set to 0.
     *
     * @link https://tools.ietf.org/html/rfc4122#section-4.1.7
     */
    public static function nil(): UuidInterface
    {
        static $nil_uuid;
        return $nil_uuid ??= (new UuidFactory())->fromString(self::NIL);
    }

    /**
     * Sometimes we may be working with a value that could either be a hex-string or
     * and instance of `UuidInterface`. This method lets us cleanly cast input
     * to a `UuidInterface` instance.
     */
    public static function instance(UuidInterface|\Stringable|string $uuid): UuidInterface
    {
        if ($uuid instanceof UuidInterface) {
            return $uuid;
        }

        return (new UuidFactory())->fromString((string)$uuid);
    }
}
