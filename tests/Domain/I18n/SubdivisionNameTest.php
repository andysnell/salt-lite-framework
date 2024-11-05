<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\I18n;

use PhoneBurner\SaltLite\Framework\Domain\I18n\RegionCode;
use PhoneBurner\SaltLite\Framework\Domain\I18n\SubdivisionCode;
use PhoneBurner\SaltLite\Framework\Domain\I18n\SubdivisionName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SubdivisionNameTest extends TestCase
{
    #[Test]
    public static function region_names_are_unique_and_non_empty(): void
    {
        $names = SubdivisionName::all();
        self::assertNotEmpty($names);
        self::assertCount(\count($names), \array_flip($names));
        self::assertCount(\count($names), \array_filter($names));
        foreach (\array_keys($names) as $key) {
            self::assertTrue(\defined(SubdivisionCode::class . '::' . $key));
        }
    }

    #[Test]
    public function region_returns_expected_names(): void
    {
        $region = SubdivisionName::region(RegionCode::CA);

        self::assertSame([
            'CA_AB' => SubdivisionName::CA_AB,
            'CA_BC' => SubdivisionName::CA_BC,
            'CA_MB' => SubdivisionName::CA_MB,
            'CA_NB' => SubdivisionName::CA_NB,
            'CA_NL' => SubdivisionName::CA_NL,
            'CA_NS' => SubdivisionName::CA_NS,
            'CA_NT' => SubdivisionName::CA_NT,
            'CA_NU' => SubdivisionName::CA_NU,
            'CA_ON' => SubdivisionName::CA_ON,
            'CA_PE' => SubdivisionName::CA_PE,
            'CA_QC' => SubdivisionName::CA_QC,
            'CA_SK' => SubdivisionName::CA_SK,
            'CA_YT' => SubdivisionName::CA_YT,
        ], $region);
    }

    #[Test]
    public function display_returns_expected_string(): void
    {
        self::assertSame('Ohio', SubdivisionName::display('US-OH'));
    }


    #[Test]
    public function display_handles_invalid_case(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @phpstan-ignore argument.type (Intentional Defect) */
        SubdivisionName::display('US-ZZ');
    }

    #[Test]
    public function short_returns_expected_string(): void
    {
        self::assertSame('OH', SubdivisionName::short('US-OH'));
    }

    #[Test]
    public function short_handles_invalid_case(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @phpstan-ignore argument.type (Intentional Defect) */
        SubdivisionName::short('US-ZZ');
    }
}
