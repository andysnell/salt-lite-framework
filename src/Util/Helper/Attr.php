<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

abstract readonly class Attr
{
    /**
     * Find attributes on any object, class-string, or reflection instance that
     * supports the "getAttributes" method. Passing a class-string as the
     * $attribute_name will filter the results to only include attributes of
     * that type.
     *
     * @template T of object
     * @param \Reflector|object|class-string $class_or_reflection
     * @param class-string<T>|null $attribute_name
     * @return ($attribute_name is null ? list<object> : array<T>)
     */
    public static function find(
        object|string $class_or_reflection,
        string|null $attribute_name = null,
        bool $use_instanceof = false,
    ): array {
        return \array_map(
            static fn(\ReflectionAttribute $reflection_attribute): object => $reflection_attribute->newInstance(),
            self::findAttributeReflections($class_or_reflection, $attribute_name, $use_instanceof),
        );
    }

    /**
     * Find the first attribute on any object, class-string, or reflection instance that
     * supports the "getAttributes" method. Passing a class-string as the
     * $attribute_name will filter the results to only include attributes of
     * that type.
     *
     * @template T of object
     * @param \Reflector|object|class-string $class_or_reflection
     * @param class-string<T>|null $attribute_name
     * @return ($attribute_name is null ? object : T)
     */
    public static function first(
        object|string $class_or_reflection,
        string|null $attribute_name = null,
        bool $use_instanceof = false,
    ): object|null {
        return (self::findAttributeReflections(
            $class_or_reflection,
            $attribute_name,
            $use_instanceof,
        )[0] ?? null)?->newInstance();
    }

    /**
     * @template T of object
     * @param \Reflector|object|class-string $class_or_reflection
     * @param class-string<T>|null $attribute_name
     * @return ($attribute_name is null ? array<\ReflectionAttribute<object>> : array<\ReflectionAttribute<T>>)
     * @todo PHP 8.5: Add support for \ReflectionConstant
     */
    private static function findAttributeReflections(
        object|string $class_or_reflection,
        string|null $attribute_name = null,
        bool $use_instanceof = false,
    ): array {
        return (match (true) {
            Type::isClassString($class_or_reflection) => new \ReflectionClass($class_or_reflection),
            ! $class_or_reflection instanceof \Reflector => new \ReflectionClass($class_or_reflection),
            $class_or_reflection instanceof \ReflectionClass, // Covers \ReflectionObject and \ReflectionEnum
                $class_or_reflection instanceof \ReflectionClassConstant, // Covers \ReflectionEnumBackedCase and \ReflectionEnumUnitCase
                $class_or_reflection instanceof \ReflectionFunctionAbstract, // Covers \ReflectionFunction and \ReflectionMethod
                $class_or_reflection instanceof \ReflectionParameter,
                $class_or_reflection instanceof \ReflectionProperty => $class_or_reflection,
            default => throw new \UnexpectedValueException('Invalid class or reflection'),
        })->getAttributes($attribute_name, $use_instanceof ? \ReflectionAttribute::IS_INSTANCEOF : 0);
    }
}
