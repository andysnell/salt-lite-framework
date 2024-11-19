<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\PhoneNumber\AreaCode;

use PhoneBurner\SaltLite\Framework\Domain\I18n\RegionCode;
use PhoneBurner\SaltLite\Framework\Domain\I18n\SubdivisionCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeLocation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AreaCodeLocationTest extends TestCase
{
    #[DataProvider('providesHappyPathTestCases')]
    #[Test]
    public function make_happy_path(array $input, string $region, array $subdivisions): void
    {
        $sut = AreaCodeLocation::make(...$input);

        self::assertSame($region, $sut->region);
        self::assertSame($subdivisions, $sut->subdivisions);
        self::assertSame($sut, AreaCodeLocation::make(...$input));
        self::assertSame(AreaCodeLocation::NANP(), AreaCodeLocation::make(AreaCodeLocation::NANP));
    }

    public static function providesHappyPathTestCases(): \Generator
    {
        yield [[AreaCodeLocation::NANP], AreaCodeLocation::NANP, []];

        yield [[RegionCode::AI], RegionCode::AI, []]; // "Anguilla",
        yield [[RegionCode::AG], RegionCode::AG, []]; // "Antigua & Barbuda",
        yield [[RegionCode::BS], RegionCode::BS, []]; // "Bahamas",
        yield [[RegionCode::BB], RegionCode::BB, []]; // "Barbados",
        yield [[RegionCode::BM], RegionCode::BM, []]; // "Bermuda",
        yield [[RegionCode::VG], RegionCode::VG, []]; // "British Virgin Islands",
        yield [[RegionCode::CA], RegionCode::CA, []]; // "Canada",
        yield [[RegionCode::KY], RegionCode::KY, []]; // "Cayman Islands",
        yield [[RegionCode::DM], RegionCode::DM, []]; // "Dominica",
        yield [[RegionCode::DO], RegionCode::DO, []]; // "Dominican Republic",
        yield [[RegionCode::GD], RegionCode::GD, []]; // "Grenada",
        yield [[RegionCode::JM], RegionCode::JM, []]; // "Jamaica",
        yield [[RegionCode::MS], RegionCode::MS, []]; // "Montserrat",
        yield [[RegionCode::SX], RegionCode::SX, []]; // "Sint Maarten",
        yield [[RegionCode::KN], RegionCode::KN, []]; // "St. Kitts & Nevis",
        yield [[RegionCode::LC], RegionCode::LC, []]; // "St. Lucia",
        yield [[RegionCode::VC], RegionCode::VC, []]; // "St. Vincent & Grenadines",
        yield [[RegionCode::TT], RegionCode::TT, []]; // "Trinidad & Tobago",
        yield [[RegionCode::TC], RegionCode::TC, []]; // "Turks & Caicos Islands",
        yield [[RegionCode::US], RegionCode::US, []]; // "United States",

        // Passing the same region twice is ok.
        yield [[RegionCode::US, RegionCode::US], RegionCode::US, []];
        yield [[AreaCodeLocation::NANP, AreaCodeLocation::NANP, AreaCodeLocation::NANP], AreaCodeLocation::NANP, []];

        yield [[SubdivisionCode::US_MO], RegionCode::US, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];
        yield [[SubdivisionCode::US_MO, RegionCode::US], RegionCode::US, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];
        yield [[RegionCode::US, SubdivisionCode::US_MO,], RegionCode::US, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];
        yield [[SubdivisionCode::US_MO, SubdivisionCode::US_MO,], RegionCode::US, [SubdivisionCode::US_MO => SubdivisionCode::US_MO]];

        yield [
            [SubdivisionCode::US_MO, SubdivisionCode::US_OH, SubdivisionCode::US_MO],
            RegionCode::US,
            [
                SubdivisionCode::US_MO => SubdivisionCode::US_MO,
                SubdivisionCode::US_OH => SubdivisionCode::US_OH,
            ],
        ];

        yield [
            [SubdivisionCode::US_OH, SubdivisionCode::US_MO],
            RegionCode::US,
            [
                SubdivisionCode::US_MO => SubdivisionCode::US_MO,
                SubdivisionCode::US_OH => SubdivisionCode::US_OH,
            ],
        ];

        yield [[SubdivisionCode::CA_NL], RegionCode::CA, [SubdivisionCode::CA_NL => SubdivisionCode::CA_NL]];

        yield [
            [SubdivisionCode::CA_NS, SubdivisionCode::CA_PE],
            RegionCode::CA,
            [
                SubdivisionCode::CA_NS => SubdivisionCode::CA_NS,
                SubdivisionCode::CA_PE => SubdivisionCode::CA_PE,
            ],
        ];

        yield [[RegionCode::AS], RegionCode::US, [SubdivisionCode::US_AS => SubdivisionCode::US_AS]];
        yield [[RegionCode::GU], RegionCode::US, [SubdivisionCode::US_GU => SubdivisionCode::US_GU]];
        yield [[RegionCode::MP], RegionCode::US, [SubdivisionCode::US_MP => SubdivisionCode::US_MP]];
        yield [[RegionCode::PR], RegionCode::US, [SubdivisionCode::US_PR => SubdivisionCode::US_PR]];
        yield [[RegionCode::VI], RegionCode::US, [SubdivisionCode::US_VI => SubdivisionCode::US_VI]];

        yield [[SubdivisionCode::US_AS], RegionCode::US, [SubdivisionCode::US_AS => SubdivisionCode::US_AS]];
        yield [[SubdivisionCode::US_GU], RegionCode::US, [SubdivisionCode::US_GU => SubdivisionCode::US_GU]];
        yield [[SubdivisionCode::US_MP], RegionCode::US, [SubdivisionCode::US_MP => SubdivisionCode::US_MP]];
        yield [[SubdivisionCode::US_PR], RegionCode::US, [SubdivisionCode::US_PR => SubdivisionCode::US_PR]];
        yield [[SubdivisionCode::US_VI], RegionCode::US, [SubdivisionCode::US_VI => SubdivisionCode::US_VI]];

        // Usually passing more than one region code would result in an exception
        // being thrown; however, US Territories are a special case and are
        // cast to their subdivision equivalent.
        yield [
            [
                RegionCode::AS,
                RegionCode::GU,
                RegionCode::MP,
                RegionCode::PR,
                RegionCode::VI,
            ],
            RegionCode::US,
            [
                SubdivisionCode::US_AS => SubdivisionCode::US_AS,
                SubdivisionCode::US_GU => SubdivisionCode::US_GU,
                SubdivisionCode::US_MP => SubdivisionCode::US_MP,
                SubdivisionCode::US_PR => SubdivisionCode::US_PR,
                SubdivisionCode::US_VI => SubdivisionCode::US_VI,
            ],
        ];
    }

    #[DataProvider('providesDifferentRegionSadPathTestCases')]
    #[Test]
    public function passing_two_different_regions_fails(array $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AreaCodeLocation Requires 1 Region');
        AreaCodeLocation::make(...$input);
    }

    public static function providesDifferentRegionSadPathTestCases(): \Generator
    {
        yield [[RegionCode::US, RegionCode::CA]];
        yield [[SubdivisionCode::US_CA, SubdivisionCode::CA_ON]];
        yield [[SubdivisionCode::US_CA, RegionCode::CA]];
    }

    #[Test]
    public function passing_invalid_region_code_fails(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid NANP Region Code: MK');
        AreaCodeLocation::make(RegionCode::MK);
    }

    #[Test]
    public function passing_invalid_subdivision_code_fails(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Undefined Subdivision Code: US-PE');
        /** @phpstan-ignore-next-line intentional defect */
        AreaCodeLocation::make('US-PE');
    }
}
