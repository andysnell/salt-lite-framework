<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;

abstract readonly class Reflect
{
    /**
     * @template T of object
     * @param T|class-string<T> $class_or_object
     * @return ReflectionClass<T>
     */
    final public static function object(object|string $class_or_object): ReflectionClass
    {
        return \is_object($class_or_object) ? new ReflectionClass($class_or_object::class) : new ReflectionClass($class_or_object);
    }

    /**
     * @param object|class-string $class_or_object
     */
    final public static function method(object|string $class_or_object, string $method): ReflectionMethod
    {
        return new ReflectionMethod($class_or_object, $method);
    }

    /**
     * @param object|class-string $class_or_object
     */
    final public static function hasProperty(object|string $class_or_object, string $property): bool
    {
        return self::object($class_or_object)->hasProperty($property);
    }

    /**
     * @param object|class-string $class_or_object
     * @todo PHP 8.0: Add test coverage when $type_set is instance of ReflectionUnionType
     * @todo PHP 8.1: Add test coverage when $type_set is instance of ReflectionIntersectionType
     */
    final public static function hasTypedProperty(object|string $class_or_object, string $property, string $type): bool
    {
        $reflection_class = self::object($class_or_object);
        if (! $reflection_class->hasProperty($property)) {
            return false;
        }

        /** @var ReflectionType|null $type_set */
        $type_set = $reflection_class->getProperty($property)->getType();
        if ($type_set === null) {
            return false;
        }

        if ($type === 'null' && $type_set->allowsNull()) {
            return true;
        }

        /** @var ReflectionNamedType[] $types */
        $types = \method_exists($type_set, 'getTypes') ? $type_set->getTypes() : [$type_set];
        foreach ($types as $named_type) {
            if ($type === $named_type->getName() && ($named_type->isBuiltin() || $type === 'self')) {
                return true;
            }

            if ($named_type->getName() === 'self' && \is_a($type, $reflection_class->getName(), true)) {
                return true;
            }

            if (\is_a($named_type->getName(), $type, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of object
     * @param T $object
     * @return T
     */
    final public static function setProperty(object $object, string $property, mixed $value = null): object
    {
        $reflection = self::object($object)->getProperty($property);
        $reflection->setValue($object, $value);

        return $object;
    }

    final public static function getProperty(object $object, string $property): mixed
    {
        return self::object($object)->getProperty($property)->getValue($object);
    }

    /**
     * @param object|class-string $class_or_object
     * @return mixed|false Value of the constant with the name $name or `false`
     *    if the constant was not found in the class.
     */
    final public static function getConstant(object|string $class_or_object, string $name)
    {
        return self::object($class_or_object)->getConstant($name);
    }

    /**
     * @param object|class-string $class_or_object
     * @return array<string,scalar|array<mixed>>
     */
    final public static function getConstants(object|string $class_or_object): array
    {
        return self::object($class_or_object)->getConstants();
    }

    /**
     * @param object|class-string $class_or_object
     * @return array<string,scalar|array<mixed>>
     */
    final public static function getPublicConstants(object|string $class_or_object): array
    {
        return self::object($class_or_object)->getConstants(\ReflectionClassConstant::IS_PUBLIC);
    }

    /**
     * Does the class or object passed as `$class_or_object` implement the interface
     * referred to by the `$interface` string? This method does essentially the
     * same thing as the PHP builtin function 'is_a' with the extra conditional
     * checks to ensure that the interface and subject class both exist.
     * Note: This method does not work to check if a class inherits from another.
     */
    final public static function implements(mixed $class_or_object, string $interface): bool
    {
        if (! \interface_exists($interface)) {
            throw new \InvalidArgumentException($interface . ' is not a valid and defined interface');
        }

        if ($class_or_object instanceof $interface) {
            return true;
        }

        return \is_string($class_or_object)
            && \class_exists($class_or_object)
            && \is_a($class_or_object, $interface, true);
    }

    /**
     * Provide the short name of the class or object passed as `$class_or_object`
     *
     * @param object|class-string $class_or_object
     */
    final public static function shortName(object|string $class_or_object): string
    {
        return self::object($class_or_object)->getShortName();
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param callable(T): void $initializer
     * @return T&object
     */
    final public static function ghost(
        string $class,
        callable $initializer,
        bool $skip_initialization_on_serialize = false,
    ): object {
        $flags = 0;
        $flags |= $skip_initialization_on_serialize ? ReflectionClass::SKIP_INITIALIZATION_ON_SERIALIZE : 0;

        $ghost = self::object($class)->newLazyGhost($initializer, $flags);
        \assert($ghost instanceof $class);

        return $ghost;
    }

    /**
     * This method wraps the passed factory in another closure that will make sure
     * that the object ultimately returned is not a lazy object. This is useful
     * when we do not necessarily know if the factory is going to give us something
     * that is lazy or not, e.g. If the factory is just resolving the class from
     * the PSR-11 container, for something like a database connection or entity,
     * the object returned by the container may be another ghost or proxy.
     *
     * @template T of object
     * @param class-string<T> $class
     * @param callable(T): T $factory
     * @return T
     */
    final public static function proxy(
        string $class,
        callable $factory,
        bool $skip_initialization_on_serialize = false,
    ): object {
        $options = 0;
        $options |= $skip_initialization_on_serialize ? ReflectionClass::SKIP_INITIALIZATION_ON_SERIALIZE : 0;

        $proxy = self::object($class)->newLazyProxy(
            /** @phpstan-ignore argument.type */
            static fn(object $object): object => self::object($object)->initializeLazyObject($factory($object)),
            $options,
        );
        \assert($proxy instanceof $class);

        return $proxy;
    }

    /**
     * Resolve the reflection for the case of an instance of an enum.
     *
     * Note that we resolve the case through \ReflectionEnum, instead of \ReflectionEnumUnitCase
     * directly so that the return value is \ReflectionEnumBackedCase when the
     * enum is backed.
     *
     * @return ($enum is \BackedEnum ? \ReflectionEnumBackedCase : \ReflectionEnumUnitCase)
     */
    public static function case(\UnitEnum $enum): \ReflectionEnumUnitCase
    {
        return new \ReflectionEnum($enum)->getCase($enum->name);
    }
}
