<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

abstract class Enum
{
    public static function values(\BackedEnum ...$enum): array
    {
        return \array_column($enum, 'value');
    }
}
