<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\I18n;

use ReflectionClass;

/**
 * ISO 3166-2 Subdivision Codes for Selected Countries
 */
final class SubdivisionCode
{
    /**
     * United States: 50 States, 1 Federal District, and 6 Territories
     *
     * Note: The six territories have both ISO 3661-1 Alpha 2 and CLDR entries
     * as "country level" regions and ISO 3661-2 entries as political subdivisions
     * of the United States. E.g. Guam is both "GU" and "US-GU".
     */
    public const string US_AK = 'US-AK';
    public const string US_AL = 'US-AL';
    public const string US_AR = 'US-AR';
    public const string US_AZ = 'US-AZ';
    public const string US_CA = 'US-CA';
    public const string US_CO = 'US-CO';
    public const string US_CT = 'US-CT';
    public const string US_DC = 'US-DC';
    public const string US_DE = 'US-DE';
    public const string US_FL = 'US-FL';
    public const string US_GA = 'US-GA';
    public const string US_HI = 'US-HI';
    public const string US_IA = 'US-IA';
    public const string US_ID = 'US-ID';
    public const string US_IL = 'US-IL';
    public const string US_IN = 'US-IN';
    public const string US_KS = 'US-KS';
    public const string US_KY = 'US-KY';
    public const string US_LA = 'US-LA';
    public const string US_MA = 'US-MA';
    public const string US_MD = 'US-MD';
    public const string US_ME = 'US-ME';
    public const string US_MI = 'US-MI';
    public const string US_MN = 'US-MN';
    public const string US_MO = 'US-MO';
    public const string US_MS = 'US-MS';
    public const string US_MT = 'US-MT';
    public const string US_NC = 'US-NC';
    public const string US_ND = 'US-ND';
    public const string US_NE = 'US-NE';
    public const string US_NH = 'US-NH';
    public const string US_NJ = 'US-NJ';
    public const string US_NM = 'US-NM';
    public const string US_NV = 'US-NV';
    public const string US_NY = 'US-NY';
    public const string US_OH = 'US-OH';
    public const string US_OK = 'US-OK';
    public const string US_OR = 'US-OR';
    public const string US_PA = 'US-PA';
    public const string US_RI = 'US-RI';
    public const string US_SC = 'US-SC';
    public const string US_SD = 'US-SD';
    public const string US_TN = 'US-TN';
    public const string US_TX = 'US-TX';
    public const string US_UT = 'US-UT';
    public const string US_VA = 'US-VA';
    public const string US_VT = 'US-VT';
    public const string US_WA = 'US-WA';
    public const string US_WI = 'US-WI';
    public const string US_WV = 'US-WV';
    public const string US_WY = 'US-WY';
    public const string US_AS = 'US-AS';
    public const string US_GU = 'US-GU';
    public const string US_MP = 'US-MP';
    public const string US_PR = 'US-PR';
    public const string US_UM = 'US-UM';
    public const string US_VI = 'US-VI';

    /**
     * Canada: 10 Provinces and 3 Territories
     */
    public const string CA_AB = 'CA-AB';
    public const string CA_BC = 'CA-BC';
    public const string CA_MB = 'CA-MB';
    public const string CA_NB = 'CA-NB';
    public const string CA_NL = 'CA-NL';
    public const string CA_NS = 'CA-NS';
    public const string CA_NT = 'CA-NT';
    public const string CA_NU = 'CA-NU';
    public const string CA_ON = 'CA-ON';
    public const string CA_PE = 'CA-PE';
    public const string CA_QC = 'CA-QC';
    public const string CA_SK = 'CA-SK';
    public const string CA_YT = 'CA-YT';

    /**
     * @return array<string, SubdivisionCode::*>
     */
    public static function all(): array
    {
        static $all;
        return $all ??= (new ReflectionClass(self::class))->getConstants();
    }

    /**
     * @phpstan-param RegionCode::*|SubdivisionCode::* $region_code
     * @return array<string, SubdivisionCode::*>
     */
    public static function region(string $region_code): array
    {
        static $region;
        $region_code = self::formatRegionCode($region_code);
        return $region[$region_code] ??= \array_filter(
            self::all(),
            static fn(string $subdivision): bool => \str_starts_with($subdivision, $region_code),
        );
    }

    /**
     * @phpstan-assert-if-true SubdivisionCode::* $subdivision_code
     */
    public static function validate(string $subdivision_code): bool
    {
        static $map;
        $map ??= \array_flip(self::all());
        return \array_key_exists($subdivision_code, $map);
    }

    private static function formatRegionCode(string $code): string
    {
        return \strtoupper(\substr($code, 0, 2));
    }
}
