<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

abstract readonly class Math
{
    final public static function clamp(int|float $value, int|float $min, int|float $max): int|float
    {
        return match (true) {
            $max < $min => throw new \UnexpectedValueException('max must be greater than or equal to min'),
            $value < $min => $min,
            $value > $max => $max,
            default => $value,
        };
    }
}
