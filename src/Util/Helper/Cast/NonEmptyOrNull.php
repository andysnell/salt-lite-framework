<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper\Cast;

abstract class NonEmptyOrNull
{
    /**
     * @return positive-int|negative-int|null
     */
    public static function integer(mixed $value): int|null
    {
        return (int)$value ?: null;
    }

    public static function float(mixed $value): float|null
    {
        return (float)$value ?: null;
    }

    /**
     * @return non-empty-string|null
     */
    public static function string(mixed $value): string|null
    {
        return (string)$value ?: null;
    }

    public static function boolean(mixed $value): true|null
    {
        return $value ? true : null;
    }

    /**
     * @return non-empty-array|null
     */
    public static function array(array|null $value): array|null
    {
        return $value ?: null;
    }
}
