<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Enum;

/**
 * @phpstan-require-implements \BackedEnum
 */
trait WithValuesStaticMethod
{
    /**
     * @return array<string, value-of<self>>
     */
    public static function values(): array
    {
        return \array_column(self::cases(), 'value', 'name');
    }
}
