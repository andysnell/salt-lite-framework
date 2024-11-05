<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode;

final readonly class AreaCodeStatus
{
    public const int INVALID = 0b00000000;
    public const int ASSIGNABLE = 0b00000001;
    public const int ASSIGNED = 0b00000010;
    public const int SCHEDULED = 0b00000100;
    public const int ACTIVE = 0b00001000;

    /**
     * @return int-mask-of<AreaCodeStatus::*>
     */
    public static function mask(int $status): int
    {
        return $status & (self::ASSIGNABLE | self::ASSIGNED | self::SCHEDULED | self::ACTIVE);
    }
}
