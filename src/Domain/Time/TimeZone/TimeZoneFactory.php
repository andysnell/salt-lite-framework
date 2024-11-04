<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone;

use DateTimeZone;

class TimeZoneFactory
{
    /**
     * @var array<string, DateTimeZone>
     */
    private static array $timezone_cache = [];

    /**
     * @var array<string, TimeZoneCollection>
     */
    private static array $collection_cache = [];

    public static function default(): DateTimeZone
    {
        return self::$timezone_cache[Tz::Chicago->value] ??= new DateTimeZone(Tz::Chicago->value);
    }

    public static function utc(): DateTimeZone
    {
        return self::$timezone_cache[Tz::Utc->value] ??= new DateTimeZone(Tz::Utc->value);
    }

    public static function make(DateTimeZone|Tz|string $time_zone): DateTimeZone
    {
        return match (true) {
            $time_zone instanceof DateTimeZone => self::$timezone_cache[$time_zone->getName()] ??= $time_zone,
            $time_zone instanceof Tz => self::$timezone_cache[$time_zone->value] ??= new DateTimeZone($time_zone->value),
            default => self::$timezone_cache[$time_zone] ??= new DateTimeZone($time_zone),
        };
    }

    public static function tryFrom(mixed $time_zone): DateTimeZone|null
    {
        try {
            return match (true) {
                $time_zone instanceof DateTimeZone, $time_zone === null => $time_zone,
                $time_zone instanceof Tz, \is_string($time_zone) => self::make($time_zone),
                default => null,
            };
        } catch (\Exception) {
            return null;
        }
    }

    public static function collect(DateTimeZone|Tz|string ...$time_zones): TimeZoneCollection
    {
        if ($time_zones === []) {
            return self::$collection_cache[''] ??= TimeZoneCollection::make();
        }

        $key = \implode('&', \array_map(static fn(DateTimeZone|Tz|string $time_zone): string => match (true) {
            $time_zone instanceof DateTimeZone => $time_zone->getName(),
            $time_zone instanceof Tz => $time_zone->value,
            default => $time_zone,
        }, $time_zones));

        return self::$collection_cache[$key] ??= TimeZoneCollection::make(...\array_map(self::make(...), $time_zones));
    }
}
