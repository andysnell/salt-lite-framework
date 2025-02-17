<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

/**
 * Helper for functions specific to resolving and working with attributes defined
 * on enum cases.
 */
class CaseAttr
{
    /**
     * Find attributes on any object, class-string, or reflection instance that
     * supports the "getAttributes" method. Passing a class-string as the
     * $attribute_name will filter the results to only include attributes of
     * that type.
     *
     * @template T of object
     * @param class-string<T>|null $attribute_name
     * @return ($attribute_name is null ? list<object> : array<T>)
     */
    public static function find(
        \UnitEnum $enum_case,
        string|null $attribute_name = null,
        bool $use_instanceof = false,
    ): array {
        return Attr::find(new \ReflectionEnumUnitCase($enum_case::class, $enum_case->name), $attribute_name, $use_instanceof);
    }

    /**
     * Find the first attribute on any object, class-string, or reflection instance that
     * supports the "getAttributes" method. Passing a class-string as the
     * $attribute_name will filter the results to only include attributes of
     * that type.
     *
     * @template T of object
     * @param class-string<T>|null $attribute_name
     * @return ($attribute_name is null ? object : T)
     */
    public static function first(
        \UnitEnum $enum_case,
        string|null $attribute_name = null,
        bool $use_instanceof = false,
    ): object|null {
        return self::find($enum_case, $attribute_name, $use_instanceof)[0] ?? null;
    }
}
