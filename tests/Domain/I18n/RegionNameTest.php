<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\I18n;

use PhoneBurner\SaltLite\Framework\Domain\I18n\RegionCode;
use PhoneBurner\SaltLite\Framework\Domain\I18n\RegionName;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class RegionNameTest extends TestCase
{
    #[Test]
    public static function region_names_are_unique_and_non_empty(): void
    {
        $names = RegionName::all();
        self::assertNotEmpty($names);
        self::assertCount(\count($names), \array_flip($names));
        /** @phpstan-ignore-next-line arrayFilter.same Intentionally filtering an array with non-falsy values */
        self::assertCount(\count($names), \array_filter($names));
        foreach (\array_keys($names) as $key) {
            self::assertTrue(\defined(RegionCode::class . '::' . $key));
        }
    }

    #[DataProvider('providesRegionCode')]
    #[Test]
    public function display_returns_expected_string(string $region_code): void
    {
        /** @phpstan-ignore-next-line intentional-defect for testing */
        self::assertSame(RegionName::display($region_code), \constant(RegionName::class . '::' . $region_code));
        /** @phpstan-ignore-next-line intentional-defect for testing */
        self::assertSame(RegionName::display(\strtolower($region_code)), \constant(RegionName::class . '::' . $region_code));
    }

    #[Test]
    public function display_handles_subdivision_codes(): void
    {
        self::assertSame('United States', RegionName::display('US'));
        self::assertSame('United States', RegionName::display('US-OH'));
        self::assertSame('Canada', RegionName::display('CA'));
        self::assertSame('Canada', RegionName::display('CA-ON'));
    }

    #[TestWith([''])]
    #[TestWith(['XX'])]
    #[TestWith(['xx'])]
    #[Test]
    public function display_throws_exception_for_invalid_codes(string $code): void
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @phpstan-ignore-next-line intentional-defect for testing */
        RegionName::display($code);
    }

    public static function providesRegionCode(): \Generator
    {
        yield from \array_map(Arr::wrap(...), RegionCode::all());
    }
}
