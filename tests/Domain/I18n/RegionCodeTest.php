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

final class RegionCodeTest extends TestCase
{
    #[Test]
    public static function region_codes_are_unique_and_non_empty(): void
    {
        $codes = RegionCode::all();
        self::assertNotEmpty($codes);
        self::assertCount(\count($codes), \array_flip($codes));
        /** @phpstan-ignore arrayFilter.same (Intentionally filtering an array with non-falsy values) */
        self::assertCount(\count($codes), \array_filter($codes));
        foreach ($codes as $key => $code) {
            self::assertMatchesRegularExpression('/^[A-Z]{2}$/', $code);
            self::assertSame($key, $code);
            self::assertTrue(\defined(RegionName::class . '::' . $key));
        }
    }

    #[DataProvider('providesRegionCodes')]
    #[Test]
    public function validate_returns_true_for_valid_region_codes(string $region_code): void
    {
        self::assertTrue(RegionCode::validate($region_code));
        /** @phpstan-ignore staticMethod.impossibleType (Intentional defect for testing) */
        self::assertFalse(RegionCode::validate(\strtolower($region_code)));
    }

    #[TestWith(['XX'])]
    #[TestWith(['xx'])]
    #[TestWith(['USA'])]
    #[TestWith([''])]
    #[Test]
    public function validate_returns_false_for_invalid_region_codes(string $region_code): void
    {
        /** @phpstan-ignore staticMethod.impossibleType (Intentional defect for testing) */
        self::assertFalse(RegionCode::validate($region_code));
        /** @phpstan-ignore staticMethod.impossibleType (Intentional defect for testing) */
        self::assertFalse(RegionCode::validate(\strtolower($region_code)));
    }

    public static function providesRegionCodes(): \Generator
    {
        yield from \array_map(Arr::wrap(...), RegionCode::all());
    }
}
