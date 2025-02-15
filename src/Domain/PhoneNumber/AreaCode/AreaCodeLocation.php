<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode;

use PhoneBurner\SaltLite\Framework\Domain\I18n\RegionCode;
use PhoneBurner\SaltLite\Framework\Domain\I18n\RegionName;
use PhoneBurner\SaltLite\Framework\Domain\I18n\SubdivisionCode;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use UnexpectedValueException;

/**
 * Collection of ISO3166-1 alpha-2 and ISO 3166-2 country/subdivision code strings
 * Also uses "NANP" to indicate an area code that is associated with all the
 * NANP regions, for example, a toll-free, or unassigned number.
 */
#[Contract]
final readonly class AreaCodeLocation
{
    public const string NANP = 'NANP';

    /**
     * United States Territories are listed as regions in the list of
     * NANP region codes for wider compatiblity, including the NANP database.
     *
     * @var array<RegionCode::*, RegionName::*>
     */
    public const array NANP_REGIONS = [
        RegionCode::AI => RegionName::AI, // "Anguilla",
        RegionCode::AG => RegionName::AG, // "Antigua & Barbuda",
        RegionCode::BS => RegionName::BS, // "Bahamas",
        RegionCode::BB => RegionName::BB, // "Barbados",
        RegionCode::BM => RegionName::BM, // "Bermuda",
        RegionCode::VG => RegionName::VG, // "British Virgin Islands",
        RegionCode::CA => RegionName::CA, // "Canada",
        RegionCode::KY => RegionName::KY, // "Cayman Islands",
        RegionCode::DM => RegionName::DM, // "Dominica",
        RegionCode::DO => RegionName::DO, // "Dominican Republic",
        RegionCode::GD => RegionName::GD, // "Grenada",
        RegionCode::JM => RegionName::JM, // "Jamaica",
        RegionCode::MS => RegionName::MS, // "Montserrat",
        RegionCode::SX => RegionName::SX, // "Sint Maarten",
        RegionCode::KN => RegionName::KN, // "St. Kitts & Nevis",
        RegionCode::LC => RegionName::LC, // "St. Lucia",
        RegionCode::VC => RegionName::VC, // "St. Vincent & Grenadines",
        RegionCode::TT => RegionName::TT, // "Trinidad & Tobago",
        RegionCode::TC => RegionName::TC, // "Turks & Caicos Islands",
        RegionCode::US => RegionName::US, // "United States",
        RegionCode::VI => RegionName::VI, // "U.S. Virgin Islands",
        RegionCode::AS => RegionName::AS, // "American Samoa",
        RegionCode::PR => RegionName::PR, // "Puerto Rico",
        RegionCode::GU => RegionName::GU, // "Guam",
        RegionCode::MP => RegionName::MP, // "Northern Mariana Islands",
    ];

    private const array UNITED_STATES_TERRITORIES = [
        RegionCode::AS => SubdivisionCode::US_AS, // "American Samoa",
        RegionCode::GU => SubdivisionCode::US_GU, // "Guam",
        RegionCode::MP => SubdivisionCode::US_MP, // "Northern Mariana Islands",
        RegionCode::PR => SubdivisionCode::US_PR, // "Puerto Rico",
        RegionCode::VI => SubdivisionCode::US_VI, // "U.S. Virgin Islands",
    ];

    /**
     * @phpstan-var RegionCode::*|self::NANP
     */
    public string $region;

    /**
     * @phpstan-var array<SubdivisionCode::*, SubdivisionCode::*>
     */
    public array $subdivisions;

    /**
     * @param array<string> $codes
     * @phpstan-assert array<SubdivisionCode::*|RegionCode::*|self::NANP> $codes
     */
    private function __construct(array $codes)
    {
        $regions = [];
        $subdivisions = [];
        foreach (\array_unique($codes) as $code) {
            if ($code === self::NANP) {
                $regions[] = self::NANP;
                continue;
            }

            $code = self::UNITED_STATES_TERRITORIES[$code] ?? $code;

            $region = \substr($code, 0, 2);
            if (! \array_key_exists($region, self::NANP_REGIONS)) {
                throw new UnexpectedValueException('Invalid NANP Region Code: ' . $region);
            }
            $regions[$region] = $region;

            if (\strlen($code) === 2) {
                continue;
            }

            if (! SubdivisionCode::validate($code)) {
                throw new UnexpectedValueException('Invalid/Undefined Subdivision Code: ' . $code);
            }
            $subdivisions[$code] = $code;
        }

        if (\count($regions) !== 1) {
            throw new \InvalidArgumentException('AreaCodeLocation Requires 1 Region');
        }

        $this->region = $regions[\array_key_first($regions)];
        $this->subdivisions = $subdivisions;
    }

    /**
     * @phpstan-param self::NANP|RegionCode::*|SubdivisionCode::* $codes
     */
    public static function make(string ...$codes): self
    {
        static $cache = [];
        \sort($codes);

        return $cache[\implode('&', $codes)] ??= new self($codes);
    }

    public static function NANP(): self
    {
        return self::make(self::NANP);
    }
}
