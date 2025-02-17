<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

abstract readonly class Type
{
    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T&object
     */
    final public static function of(string $type, object $value): object
    {
        return $value instanceof $type ? $value : throw new \UnexpectedValueException(
            \sprintf('Expected an instance of %s, but got %s', $type, $value::class),
        );
    }

    /**
     * @phpstan-assert-if-true object|class-string $value
     */
    final public static function isClass(mixed $value): bool
    {
        return match (true) {
            \is_object($value) => true,
            \is_string($value) => \class_exists($value),
            default => false,
        };
    }

    /**
     * @phpstan-assert-if-true class-string $value
     */
    final public static function isClassString(mixed $value): bool
    {
        return \is_string($value) && (\class_exists($value) || \interface_exists($value));
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     * @phpstan-assert-if-true class-string<T> $value
     */
    final public static function isClassStringOf(string $type, mixed $value): bool
    {
        return \is_string($value) && \is_a($value, $type, true);
    }

    final public static function isStreamResource(mixed $value): bool
    {
        return \is_resource($value) && \get_resource_type($value) === 'stream';
    }
}
