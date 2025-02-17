<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Time\TimeZone;

use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\TimeZoneCollection;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\TimeZoneFactory;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\Tz;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tz::class)]
#[CoversClass(TimeZoneFactory::class)]
final class TimeZoneFactoryTest extends TestCase
{
    /**
     * @param value-of<Tz>&string $time_zone_name
     */
    #[DataProvider('providesTimeZoneNames')]
    #[Test]
    public function make_returns_memoized_time_zone(string $time_zone_name): void
    {
        $tz = TimeZoneFactory::make($time_zone_name);
        self::assertSame($tz, TimeZoneFactory::make($time_zone_name));
        self::assertSame($time_zone_name, $tz->getName());
    }

    public static function providesTimeZoneNames(): \Generator
    {
        yield from \array_map(Arr::wrap(...), \array_column(Tz::cases(), 'value'));
    }

    #[Test]
    public function collect_returns_empty_time_zone_collection(): void
    {
        $collection = TimeZoneFactory::collect();
        self::assertEmpty($collection);
        self::assertSame($collection, TimeZoneFactory::collect());
        self::assertEquals($collection, TimeZoneCollection::make());
    }

    #[Test]
    public function collect_returns_memoized_time_zone_collection(): void
    {
        $collection = TimeZoneFactory::collect(
            Tz::NewYork,
            Tz::Chicago,
            Tz::Denver,
            Tz::LosAngeles,
        );

        self::assertCount(4, $collection);

        self::assertSame($collection, TimeZoneFactory::collect(
            Tz::NewYork,
            Tz::Chicago,
            Tz::Denver,
            Tz::LosAngeles,
        ));

        self::assertSame($collection, TimeZoneFactory::collect(
            new \DateTimeZone(Tz::NewYork->value),
            new \DateTimeZone(Tz::Chicago->value),
            new \DateTimeZone(Tz::Denver->value),
            new \DateTimeZone(Tz::LosAngeles->value),
        ));
    }

    #[Test]
    public function default_returns_expected_memoized_timezone(): void
    {
        $default = TimeZoneFactory::default();
        self::assertSame($default, TimeZoneFactory::default());
        self::assertSame(Tz::Chicago->value, $default->getName());
        self::assertSame(Tz::Chicago->value, TimeZoneFactory::default()->getName());
    }
}
