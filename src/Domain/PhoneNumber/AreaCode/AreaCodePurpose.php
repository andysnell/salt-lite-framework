<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode;

enum AreaCodePurpose: int
{
    case GeneralPurpose = 0;
    case PersonalCommunication = 1;
    case CanadianNonGeographicTariffed = 2;
    case CanadianNonGeographic = 3;
    case InterexchangeCarrier = 4;
    case Government = 5;
    case TollFree = 6;
    case PremiumService = 7;

    public static function lookup(AreaCodeAware $area_code): self
    {
        return match ($area_code->getAreaCode()->npa) {
            500, 521, 522, 523, 524, 525, 526, 527, 528, 529, 533, 544, 566, 577, 588 => self::PersonalCommunication,
            600 => self::CanadianNonGeographicTariffed,
            622 => self::CanadianNonGeographic,
            700 => self::InterexchangeCarrier,
            710 => self::Government,
            800, 833, 844, 855, 866, 877, 888 => self::TollFree,
            900 => self::PremiumService,
            default => self::GeneralPurpose,
        };
    }
}
