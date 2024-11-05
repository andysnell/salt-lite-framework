<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\I18n;

use ReflectionClass;
use UnexpectedValueException;

/**
 * Maps ISO 3166-1 Alpha 2 Country Codes to CLDR Region Name for the "en" Locale
 * The names were generated with the giggsey/locale, localization library
 * required by giggsey/libphonenumber-for-php. These should be the "standard"
 * names for the country, used by companies like Apple and Google, as opposed to
 * the "official", potentially politically problematic, ISO 3166 country names.
 *
 * @see \Giggsey\Locale\Locale::getAllCountriesForLocale()
 */
final class RegionName
{
    public const string AC = 'Ascension Island';
    public const string AD = 'Andorra';
    public const string AE = 'United Arab Emirates';
    public const string AF = 'Afghanistan';
    public const string AG = 'Antigua & Barbuda';
    public const string AI = 'Anguilla';
    public const string AL = 'Albania';
    public const string AM = 'Armenia';
    public const string AO = 'Angola';
    public const string AQ = 'Antarctica';
    public const string AR = 'Argentina';
    public const string AS = 'American Samoa';
    public const string AT = 'Austria';
    public const string AU = 'Australia';
    public const string AW = 'Aruba';
    public const string AX = 'Åland Islands';
    public const string AZ = 'Azerbaijan';
    public const string BA = 'Bosnia & Herzegovina';
    public const string BB = 'Barbados';
    public const string BD = 'Bangladesh';
    public const string BE = 'Belgium';
    public const string BF = 'Burkina Faso';
    public const string BG = 'Bulgaria';
    public const string BH = 'Bahrain';
    public const string BI = 'Burundi';
    public const string BJ = 'Benin';
    public const string BL = 'St. Barthélemy';
    public const string BM = 'Bermuda';
    public const string BN = 'Brunei';
    public const string BO = 'Bolivia';
    public const string BQ = 'Caribbean Netherlands';
    public const string BR = 'Brazil';
    public const string BS = 'Bahamas';
    public const string BT = 'Bhutan';
    public const string BV = 'Bouvet Island';
    public const string BW = 'Botswana';
    public const string BY = 'Belarus';
    public const string BZ = 'Belize';
    public const string CA = 'Canada';
    public const string CC = 'Cocos (Keeling) Islands';
    public const string CD = 'Congo - Kinshasa';
    public const string CF = 'Central African Republic';
    public const string CG = 'Congo - Brazzaville';
    public const string CH = 'Switzerland';
    public const string CI = 'Côte d’Ivoire';
    public const string CK = 'Cook Islands';
    public const string CL = 'Chile';
    public const string CM = 'Cameroon';
    public const string CN = 'China';
    public const string CO = 'Colombia';
    public const string CR = 'Costa Rica';
    public const string CU = 'Cuba';
    public const string CV = 'Cape Verde';
    public const string CW = 'Curaçao';
    public const string CX = 'Christmas Island';
    public const string CY = 'Cyprus';
    public const string CZ = 'Czechia';
    public const string DE = 'Germany';
    public const string DG = 'Diego Garcia';
    public const string DJ = 'Djibouti';
    public const string DK = 'Denmark';
    public const string DM = 'Dominica';
    public const string DO = 'Dominican Republic';
    public const string DZ = 'Algeria';
    public const string EA = 'Ceuta & Melilla';
    public const string EC = 'Ecuador';
    public const string EE = 'Estonia';
    public const string EG = 'Egypt';
    public const string EH = 'Western Sahara';
    public const string ER = 'Eritrea';
    public const string ES = 'Spain';
    public const string ET = 'Ethiopia';
    public const string FI = 'Finland';
    public const string FJ = 'Fiji';
    public const string FK = 'Falkland Islands';
    public const string FM = 'Micronesia';
    public const string FO = 'Faroe Islands';
    public const string FR = 'France';
    public const string GA = 'Gabon';
    public const string GB = 'United Kingdom';
    public const string GD = 'Grenada';
    public const string GE = 'Georgia';
    public const string GF = 'French Guiana';
    public const string GG = 'Guernsey';
    public const string GH = 'Ghana';
    public const string GI = 'Gibraltar';
    public const string GL = 'Greenland';
    public const string GM = 'Gambia';
    public const string GN = 'Guinea';
    public const string GP = 'Guadeloupe';
    public const string GQ = 'Equatorial Guinea';
    public const string GR = 'Greece';
    public const string GS = 'South Georgia & South Sandwich Islands';
    public const string GT = 'Guatemala';
    public const string GU = 'Guam';
    public const string GW = 'Guinea-Bissau';
    public const string GY = 'Guyana';
    public const string HK = 'Hong Kong SAR China';
    public const string HN = 'Honduras';
    public const string HR = 'Croatia';
    public const string HT = 'Haiti';
    public const string HU = 'Hungary';
    public const string IC = 'Canary Islands';
    public const string ID = 'Indonesia';
    public const string IE = 'Ireland';
    public const string IL = 'Israel';
    public const string IM = 'Isle of Man';
    public const string IN = 'India';
    public const string IO = 'British Indian Ocean Territory';
    public const string IQ = 'Iraq';
    public const string IR = 'Iran';
    public const string IS = 'Iceland';
    public const string IT = 'Italy';
    public const string JE = 'Jersey';
    public const string JM = 'Jamaica';
    public const string JO = 'Jordan';
    public const string JP = 'Japan';
    public const string KE = 'Kenya';
    public const string KG = 'Kyrgyzstan';
    public const string KH = 'Cambodia';
    public const string KI = 'Kiribati';
    public const string KM = 'Comoros';
    public const string KN = 'St. Kitts & Nevis';
    public const string KP = 'North Korea';
    public const string KR = 'South Korea';
    public const string KW = 'Kuwait';
    public const string KY = 'Cayman Islands';
    public const string KZ = 'Kazakhstan';
    public const string LA = 'Laos';
    public const string LB = 'Lebanon';
    public const string LC = 'St. Lucia';
    public const string LI = 'Liechtenstein';
    public const string LK = 'Sri Lanka';
    public const string LR = 'Liberia';
    public const string LS = 'Lesotho';
    public const string LT = 'Lithuania';
    public const string LU = 'Luxembourg';
    public const string LV = 'Latvia';
    public const string LY = 'Libya';
    public const string MA = 'Morocco';
    public const string MC = 'Monaco';
    public const string MD = 'Moldova';
    public const string ME = 'Montenegro';
    public const string MF = 'St. Martin';
    public const string MG = 'Madagascar';
    public const string MH = 'Marshall Islands';
    public const string MK = 'North Macedonia';
    public const string ML = 'Mali';
    public const string MM = 'Myanmar (Burma)';
    public const string MN = 'Mongolia';
    public const string MO = 'Macao SAR China';
    public const string MP = 'Northern Mariana Islands';
    public const string MQ = 'Martinique';
    public const string MR = 'Mauritania';
    public const string MS = 'Montserrat';
    public const string MT = 'Malta';
    public const string MU = 'Mauritius';
    public const string MV = 'Maldives';
    public const string MW = 'Malawi';
    public const string MX = 'Mexico';
    public const string MY = 'Malaysia';
    public const string MZ = 'Mozambique';
    public const string NA = 'Namibia';
    public const string NC = 'New Caledonia';
    public const string NE = 'Niger';
    public const string NF = 'Norfolk Island';
    public const string NG = 'Nigeria';
    public const string NI = 'Nicaragua';
    public const string NL = 'Netherlands';
    public const string NO = 'Norway';
    public const string NP = 'Nepal';
    public const string NR = 'Nauru';
    public const string NU = 'Niue';
    public const string NZ = 'New Zealand';
    public const string OM = 'Oman';
    public const string PA = 'Panama';
    public const string PE = 'Peru';
    public const string PF = 'French Polynesia';
    public const string PG = 'Papua New Guinea';
    public const string PH = 'Philippines';
    public const string PK = 'Pakistan';
    public const string PL = 'Poland';
    public const string PM = 'St. Pierre & Miquelon';
    public const string PN = 'Pitcairn Islands';
    public const string PR = 'Puerto Rico';
    public const string PS = 'Palestinian Territories';
    public const string PT = 'Portugal';
    public const string PW = 'Palau';
    public const string PY = 'Paraguay';
    public const string QA = 'Qatar';
    public const string RE = 'Réunion';
    public const string RO = 'Romania';
    public const string RS = 'Serbia';
    public const string RU = 'Russia';
    public const string RW = 'Rwanda';
    public const string SA = 'Saudi Arabia';
    public const string SB = 'Solomon Islands';
    public const string SC = 'Seychelles';
    public const string SD = 'Sudan';
    public const string SE = 'Sweden';
    public const string SG = 'Singapore';
    public const string SH = 'St. Helena';
    public const string SI = 'Slovenia';
    public const string SJ = 'Svalbard & Jan Mayen';
    public const string SK = 'Slovakia';
    public const string SL = 'Sierra Leone';
    public const string SM = 'San Marino';
    public const string SN = 'Senegal';
    public const string SO = 'Somalia';
    public const string SR = 'Suriname';
    public const string SS = 'South Sudan';
    public const string ST = 'São Tomé & Príncipe';
    public const string SV = 'El Salvador';
    public const string SX = 'Sint Maarten';
    public const string SY = 'Syria';
    public const string SZ = 'Eswatini';
    public const string TA = 'Tristan da Cunha';
    public const string TC = 'Turks & Caicos Islands';
    public const string TD = 'Chad';
    public const string TF = 'French Southern Territories';
    public const string TG = 'Togo';
    public const string TH = 'Thailand';
    public const string TJ = 'Tajikistan';
    public const string TK = 'Tokelau';
    public const string TL = 'Timor-Leste';
    public const string TM = 'Turkmenistan';
    public const string TN = 'Tunisia';
    public const string TO = 'Tonga';
    public const string TR = 'Turkey';
    public const string TT = 'Trinidad & Tobago';
    public const string TV = 'Tuvalu';
    public const string TW = 'Taiwan';
    public const string TZ = 'Tanzania';
    public const string UA = 'Ukraine';
    public const string UG = 'Uganda';
    public const string UM = 'U.S. Outlying Islands';
    public const string US = 'United States';
    public const string UY = 'Uruguay';
    public const string UZ = 'Uzbekistan';
    public const string VA = 'Vatican City';
    public const string VC = 'St. Vincent & Grenadines';
    public const string VE = 'Venezuela';
    public const string VG = 'British Virgin Islands';
    public const string VI = 'U.S. Virgin Islands';
    public const string VN = 'Vietnam';
    public const string VU = 'Vanuatu';
    public const string WF = 'Wallis & Futuna';
    public const string WS = 'Samoa';
    public const string XK = 'Kosovo';
    public const string YE = 'Yemen';
    public const string YT = 'Mayotte';
    public const string ZA = 'South Africa';
    public const string ZM = 'Zambia';
    public const string ZW = 'Zimbabwe';
    public const string ZZ = 'Unknown Region';

    /**
     * @return array<RegionCode::*, RegionName::*>
     */
    public static function all(): array
    {
        static $all;
        return $all ??= (new ReflectionClass(self::class))->getConstants();
    }

    /**
     * @phpstan-param RegionCode::*|SubdivisionCode::* $region_code
     */
    public static function display(string $region_code): string
    {
        $class_constant = self::class . '::' . \strtoupper(\substr($region_code, 0, 2));
        if (\defined($class_constant)) {
            return \constant($class_constant);
        }

        throw new UnexpectedValueException('Invalid ISO3166-1 Region Code');
    }
}
