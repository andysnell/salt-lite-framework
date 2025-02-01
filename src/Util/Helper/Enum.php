<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

abstract readonly class Enum
{
    final public static function values(\BackedEnum ...$enum): array
    {
        return \array_column($enum, 'value');
    }
}
