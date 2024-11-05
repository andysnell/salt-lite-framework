<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\PhoneNumber\AreaCode;

use PhoneBurner\SaltLite\Framework\Domain\I18n\RegionCode;
use PhoneBurner\SaltLite\Framework\Domain\I18n\SubdivisionCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeAware;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeData;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeLocation;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodePurpose;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeStatus;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\TimeZoneFactory;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\Tz;
use PhoneBurner\SaltLite\Framework\Tests\Fixtures\AreaCodeMetadata;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(AreaCode::class)]
#[CoversClass(AreaCodeData::class)]
class AreaCodeTest extends TestCase
{
    #[Test]
    public function all_returns_collection_of_all_area_codes(): void
    {
        $area_codes = AreaCode::all();
        self::assertCount(800, $area_codes);
        $npa_values = \array_column([...$area_codes], 'npa', 'npa');
        self::assertCount(800, $npa_values);
        foreach ($npa_values as $npa) {
            self::assertGreaterThanOrEqual(200, $npa);
            self::assertLessThanOrEqual(999, $npa);
        }
    }

    #[Test]
    public function active_returns_collection_of_active_area_codes(): void
    {
        $area_codes = AreaCode::active();
        self::assertCount(467, $area_codes);
        self::assertTrue($area_codes->contains(AreaCode::make(314)));
        self::assertFalse($area_codes->contains(AreaCode::make(911)));
    }

    #[TestWith([23])]
    #[TestWith([0])]
    #[TestWith([9999])]
    #[TestWith([100])]
    #[TestWith([-314])]
    #[Test]
    public function bad_hydrate(int $area_code): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid Area Code NPA Value');

        AreaCode::make($area_code);
    }

    #[TestWith([23])]
    #[TestWith([0])]
    #[TestWith([9999])]
    #[TestWith([100])]
    #[TestWith([-314])]
    #[Test]
    public function bad_hydrate_with_tryFrom_returns_null(int $area_code): void
    {
        self::assertNull(AreaCode::tryFrom($area_code));
    }

    #[DataProvider('providesValidAreaCodeNpaValues')]
    #[Test]
    public function can_hydrate_memoized_area_code(int $npa): void
    {
        $area_code = AreaCode::make($npa);

        self::assertSame($npa, $area_code->npa);
        self::assertSame((string)$npa, (string)$area_code);
        self::assertSame(AreaCode::make($npa), $area_code);
        self::assertSame(AreaCode::make((string)$npa), $area_code);
        self::assertSame($area_code, $area_code->getAreaCode());
        self::assertSame($area_code, AreaCode::tryFrom($area_code));
        self::assertSame($area_code, AreaCode::tryFrom((string)$npa));
        self::assertSame($area_code, AreaCode::tryFrom($npa));
    }

    #[DataProvider('providesValidAreaCodeNpaValues')]
    #[Test]
    public function can_hydrate_from_area_code_aware(int $npa): void
    {
        $test = new class ($npa) implements AreaCodeAware {
            public function __construct(private readonly int $npa)
            {
            }

            public function getAreaCode(): AreaCode
            {
                return AreaCode::make($this->npa);
            }
        };

        self::assertSame(AreaCode::make($npa), AreaCode::make($test));
    }

    #[DataProvider('providesValidAreaCodeNpaValues')]
    #[Test]
    public function it_can_be_serialized_and_deserialized(int $npa): void
    {
        $area_code = AreaCode::make($npa);

        $serialized = \serialize($area_code);
        $deserialized = \unserialize($serialized, ['allowed_classes' => [AreaCode::class]]);

        self::assertInstanceOf(AreaCode::class, $deserialized);
        self::assertEquals($area_code, $deserialized);
    }

    public static function providesValidAreaCodeNpaValues(): \Generator
    {
        yield from \array_map(Arr::wrap(...), \range(200, 999));
    }

    #[DataProvider('providesAreaCodeMetadata')]
    #[Test]
    public function area_codes_have_expected_metadata(AreaCodeMetadata $metadata): void
    {
        $area_code = AreaCode::make($metadata->npa);

        self::assertSame($metadata->npa, $area_code->npa);
        self::assertSame($metadata->status, $area_code->status & $metadata->status);
        self::assertSame($metadata->purpose, $area_code->purpose);
        self::assertSame($metadata->region, $area_code->location->region);
        self::assertSame($metadata->subdivisions, \array_values($area_code->location->subdivisions));
        self::assertSame($metadata->time_zones, [...$area_code->time_zones]);
        self::assertSame($metadata->time_zones, [...$area_code->getTimeZones()]);
        self::assertSame($metadata->is_active, $area_code->isActive());
        self::assertSame((string)$metadata->npa, (string)$area_code);
    }

    public static function providesAreaCodeMetadata(): \Generator
    {
        yield [
            new AreaCodeMetadata(
                314,
                AreaCodeStatus::ACTIVE,
                AreaCodePurpose::GeneralPurpose,
                RegionCode::US,
                [SubdivisionCode::US_MO],
                [TimeZoneFactory::make(Tz::Chicago)],
                true,
                false,
            ),
        ];

        yield [
            new AreaCodeMetadata(
                800,
                AreaCodeStatus::ACTIVE,
                AreaCodePurpose::TollFree,
                AreaCodeLocation::NANP,
                [],
                [...TimeZoneFactory::collect(...[
                    Tz::Adak,
                    Tz::Anchorage,
                    Tz::Barbados,
                    Tz::Chicago,
                    Tz::Denver,
                    Tz::Edmonton,
                    Tz::FortNelson,
                    Tz::GrandTurk,
                    Tz::Halifax,
                    Tz::Jamaica,
                    Tz::LosAngeles,
                    Tz::NewYork,
                    Tz::Panama,
                    Tz::Phoenix,
                    Tz::PuertoRico,
                    Tz::Regina,
                    Tz::SantoDomingo,
                    Tz::StJohns,
                    Tz::Toronto,
                    Tz::Vancouver,
                    Tz::Winnipeg,
                    Tz::Bermuda,
                    Tz::Guam,
                    Tz::Honolulu,
                    Tz::PagoPago,
                ])],
                true,
                false,
            ),
        ];

        yield [
            new AreaCodeMetadata(
                911,
                AreaCodeStatus::INVALID,
                AreaCodePurpose::GeneralPurpose,
                AreaCodeLocation::NANP,
                [],
                [],
                false,
                false,
            ),
        ];
    }
}
