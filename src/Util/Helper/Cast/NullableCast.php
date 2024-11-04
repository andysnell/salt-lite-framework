<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper\Cast;

abstract class NullableCast
{
    public static function integer(mixed $value): int|null
    {
        return match (true) {
            \is_int($value), $value === null => $value,
            \is_scalar($value) => (int)$value,
            $value instanceof \BackedEnum => (int)$value->value,
            default => throw new \TypeError('Invalid type for integer cast: ' . \gettype($value)),
        };
    }

    public static function float(mixed $value): float|null
    {
        return match (true) {
            \is_float($value), $value === null => $value,
            \is_scalar($value) => (float)$value,
            $value instanceof \BackedEnum => (float)$value->value,
            default => throw new \TypeError('Invalid type for float cast: ' . \gettype($value)),
        };
    }

    /**
     * @return ($value is null ? null : string)
     */
    public static function string(mixed $value): string|null
    {
        return match (true) {
            \is_string($value), $value === null => $value,
            \is_scalar($value), $value instanceof \Stringable => (string)$value,
            $value instanceof \BackedEnum => (string)$value->value,
            default => throw new \TypeError('Invalid type for string cast: ' . \gettype($value)),
        };
    }

    public static function boolean(mixed $value): bool|null
    {
        return $value !== null ? (bool)$value : null;
    }
}
