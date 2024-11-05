<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\I18n;

use ReflectionClass;
use UnexpectedValueException;

/**
 * Mapping of ISO 3166-2 Subdivision Codes for Selected Countries to Common Name
 */
final class SubdivisionName
{
    /**
     * United States: 50 States, 1 Federal District, and 6 Territories
     *
     * Note: The six territories have both ISO 3661-1 Alpha 2 and CLDR entries
     * as "country level" regions and ISO 3661-2 entries as political subdivisions
     * of the United States. E.g. Guam is both "GU" and "US-GU".
     */
    public const string US_AK = 'Alaska';
    public const string US_AL = 'Alabama';
    public const string US_AR = 'Arkansas';
    public const string US_AZ = 'Arizona';
    public const string US_CA = 'California';
    public const string US_CO = 'Colorado';
    public const string US_CT = 'Connecticut';
    public const string US_DC = 'District of Columbia';
    public const string US_DE = 'Delaware';
    public const string US_FL = 'Florida';
    public const string US_GA = 'Georgia';
    public const string US_HI = 'Hawaii';
    public const string US_IA = 'Iowa';
    public const string US_ID = 'Idaho';
    public const string US_IL = 'Illinois';
    public const string US_IN = 'Indiana';
    public const string US_KS = 'Kansas';
    public const string US_KY = 'Kentucky';
    public const string US_LA = 'Louisiana';
    public const string US_MA = 'Massachusetts';
    public const string US_MD = 'Maryland';
    public const string US_ME = 'Maine';
    public const string US_MI = 'Michigan';
    public const string US_MN = 'Minnesota';
    public const string US_MO = 'Missouri';
    public const string US_MS = 'Mississippi';
    public const string US_MT = 'Montana';
    public const string US_NC = 'North Carolina';
    public const string US_ND = 'North Dakota';
    public const string US_NE = 'Nebraska';
    public const string US_NH = 'New Hampshire';
    public const string US_NJ = 'New Jersey';
    public const string US_NM = 'New Mexico';
    public const string US_NV = 'Nevada';
    public const string US_NY = 'New York';
    public const string US_OH = 'Ohio';
    public const string US_OK = 'Oklahoma';
    public const string US_OR = 'Oregon';
    public const string US_PA = 'Pennsylvania';
    public const string US_RI = 'Rhode Island';
    public const string US_SC = 'South Carolina';
    public const string US_SD = 'South Dakota';
    public const string US_TN = 'Tennessee';
    public const string US_TX = 'Texas';
    public const string US_UT = 'Utah';
    public const string US_VA = 'Virginia';
    public const string US_VT = 'Vermont';
    public const string US_WA = 'Washington';
    public const string US_WI = 'Wisconsin';
    public const string US_WV = 'West Virginia';
    public const string US_WY = 'Wyoming';
    public const string US_AS = RegionName::AS;
    public const string US_GU = RegionName::GU;
    public const string US_MP = RegionName::MP;
    public const string US_PR = RegionName::PR;
    public const string US_UM = RegionName::UM;
    public const string US_VI = RegionName::VI;

    /**
     * Canada: 10 Provinces and 3 Territories
     */
    public const string CA_AB = 'Alberta';
    public const string CA_BC = 'British Columbia';
    public const string CA_MB = 'Manitoba';
    public const string CA_NB = 'New Brunswick';
    public const string CA_NL = 'Newfoundland and Labrador';
    public const string CA_NS = 'Nova Scotia';
    public const string CA_NT = 'Northwest Territories';
    public const string CA_NU = 'Nunavut';
    public const string CA_ON = 'Ontario';
    public const string CA_PE = 'Prince Edward Island';
    public const string CA_QC = 'Quebec';
    public const string CA_SK = 'Saskatchewan';
    public const string CA_YT = 'Yukon';

    public static function all(): array
    {
        static $all;
        return $all ??= (new ReflectionClass(self::class))->getConstants();
    }

    /**
     * @phpstan-param RegionCode::*|SubdivisionCode::* $region_code
     * @return array<string, SubdivisionName::*>
     */
    public static function region(string $region_code): array
    {
        static $region;
        $region_code = self::formatRegionCode($region_code);
        return $region[$region_code] ??= \array_filter(
            self::all(),
            static fn(string $subdivision): bool => \str_starts_with($subdivision, $region_code),
            \ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * @phpstan-param SubdivisionCode::* $subdivision_code
     */
    public static function display(string $subdivision_code): string
    {
        $class_constant = self::class . '::' . self::formatSubdivisionCode($subdivision_code);
        if (\defined($class_constant)) {
            return \constant($class_constant);
        }

        throw new UnexpectedValueException('Invalid or Unsupported ISO3166-2 Subdivision Code');
    }

    /**
     * @phpstan-param SubdivisionCode::* $subdivision_code
     */
    public static function short(string $subdivision_code): string
    {
        $subdivision_code = self::formatSubdivisionCode($subdivision_code);
        if (\defined(self::class . '::' . $subdivision_code)) {
            return \substr($subdivision_code, 3);
        }

        throw new UnexpectedValueException('Invalid or Unsupported ISO3166-2 Subdivision Code');
    }

    private static function formatSubdivisionCode(string $subdivision_code): string
    {
        return \strtoupper(\str_replace('-', '_', $subdivision_code));
    }

    private static function formatRegionCode(string $code): string
    {
        return \strtoupper(\substr($code, 0, 2));
    }
}
