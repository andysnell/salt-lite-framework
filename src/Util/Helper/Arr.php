<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use ArrayAccess;
use Iterator;
use IteratorIterator;
use JsonException;
use PhoneBurner\SaltLite\Framework\Domain\Arrayable;
use Traversable;

abstract class Arr
{
    /**
     * Returns true if the $array is an array primitive or an object that
     * implements `ArrayAccess`. Note that objects that implement `ArrayAccess`
     * are not required to be castable into arrays.
     *
     * @param array<mixed>|ArrayAccess<mixed,mixed> $array
     */
    public static function accessible($array): bool
    {
        return \is_array($array) || $array instanceof ArrayAccess;
    }

    /**
     * Returns true if passed an array or an instance of Arrayable or Traversable,
     */
    public static function arrayable(mixed $value): bool
    {
        return \is_iterable($value) || $value instanceof Arrayable;
    }

    /**
     * The PHP array_* functions only work with array primitives; however, it is
     * not uncommon to have a $variable that is known to have array-like behavior
     * but not know if it is an array or an instance of Traversable. This method
     * allows for clean conversion without knowing the $value $type. It will
     * return the $value if it is already an array or convert array-like things
     * including instances of iterable or Arrayable. We intentionally do not
     * cast objects as arrays with (array) because the result can be unexpected
     * with the way PHP handles non-public object properties and considering all
     * anonymous functions are actually object instances of \Closure.
     *
     * @param Arrayable|iterable<mixed>|Traversable $value
     */
    public static function array(mixed $value): array
    {
        return match (true) {
            \is_array($value) => $value,
            $value instanceof Arrayable => $value->toArray(),
            $value instanceof Traversable => \iterator_to_array($value),
            default => throw new \InvalidArgumentException('Arr::array Cannot Convert Value to Array'),
        };
    }

    /**
     * The `iterable` pseudotype is the union of `array|Traversable`, and can be
     * used for both parameter and return typing; however, almost all the
     * PHP functions for working with iterable things will only accept `array`
     * or a `Traversable` object. We commonly need one or the other, and by type
     * hinting on `iterable`, we don't know at runtime what we are working with.
     * This helper method takes any iterable and returns an `Iterator`.
     * The `yield from` construct is used to convert the array into an instance
     * of Generator, which preserves both associative and integer array keys.
     * This also works with any class that implements Arrayable. If an object is
     * an instance of both `Traversable` and `Arrayable`, the method returns the
     * object like other `Traversable` objects.
     *
     * @template T
     * @param Arrayable|iterable<T> $value
     * @return Iterator<T>
     */
    public static function iterable($value): Iterator
    {
        if (\is_array($value)) {
            return (static fn() => yield from $value)();
        }

        if ($value instanceof Iterator) {
            return $value;
        }

        if ($value instanceof Traversable) {
            return new IteratorIterator($value);
        }

        if ($value instanceof Arrayable) {
            return self::iterable($value->toArray());
        }

        throw new \InvalidArgumentException('Arr::iterable Cannot Convert Value to Traversable');
    }

    /**
     * This function avoids having to assign the array to a variable before
     * calling the `reset` builtin. It also avoids the problem that the return
     * value of `reset` called on an empty array is identical to an array where
     * the first element is `false`. If the array is empty, this method will
     * return null. (This is now indistinguishable from an array where the first
     * element is `null`; however, this should be significantly less impactful.)
     * It also supports returning the first value of any `iterable`, not just arrays.
     *
     * @param iterable<mixed>|Arrayable $value
     * @return mixed|null
     */
    public static function first(mixed $value): mixed
    {
        if (\is_iterable($value)) {
            foreach ($value as $first) {
                return $first;
            }

            return null;
        }

        if ($value instanceof Arrayable) {
            return self::first($value->toArray());
        }

        throw new \InvalidArgumentException('Arr::first Cannot Iterate Over Value');
    }

    /**
     * Check if a key is set and has a non-null value from an arbitrary array or
     * object that implements the ArrayAccess interface, supporting dot notation
     * to search a deeply nested array with a composite string key.
     *
     * @param array<mixed>|ArrayAccess<mixed,mixed> $array
     */
    public static function has(string $key, mixed $array): bool
    {
        if (! self::accessible($array)) {
            throw new \InvalidArgumentException('Array Argument Must Be Array or ArrayAccess');
        }

        if (! $array) {
            return false;
        }

        if (isset($array[$key])) {
            return true;
        }

        if (! \str_contains($key, '.')) {
            return false;
        }

        foreach (\explode('.', $key) as $subkey) {
            if (! self::accessible($array) || ! isset($array[$subkey])) {
                return false;
            }
            $array = $array[$subkey];
        }

        return true;
    }

    /**
     * Lookup a value from an arbitrary array or object that implements the
     * ArrayAccess interface, supporting dot notation to search a deeply nested
     * array with a composite string key. If the key does not exist or is null,
     * the default value will be returned. If the $default argument is
     * `callable`, it will be evaluated and the result returned.
     *
     * @param array<mixed>|ArrayAccess<mixed, mixed> $array
     * @param callable|mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $array, mixed $default = null)
    {
        if (! static::accessible($array)) {
            throw new \InvalidArgumentException('Array Argument Must Be Array or ArrayAccess');
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        // If the $key is not in dot notation return the default early.
        if (! \str_contains($key, '.')) {
            return Func::value($default);
        }

        foreach (\explode('.', $key) as $subkey) {
            if (! static::accessible($array) || ! isset($array[$subkey])) {
                return Func::value($default);
            }
            $array = $array[$subkey];
        }

        return $array;
    }

    /**
     * Returns the passed value, recursively casting instances of `Arrayable` and
     * `Traversable` into arrays.
     */
    public static function value(mixed $value): mixed
    {
        return self::arrayable($value) ? \array_map(__METHOD__, self::array($value)) : $value;
    }

    /**
     * If the $value is not an array or an instance of Arrayable or Traversable
     * return the value wrapped in an array, i.e. `[$value]`, otherwise, cast
     * the array, Arrayable or Traversable to an array and return.
     *
     * @return array<mixed>
     */
    public static function wrap(mixed $value): array
    {
        return self::arrayable($value) ? self::array($value) : [$value];
    }

    /**
     * Fill for PHP 8.1 builtin function: array_is_list()
     *
     * @link https://php.watch/versions/8.1/array_is_list
     * @param array<mixed> $array
     */
    public static function isList(array $array): bool
    {
        $keys = \array_keys($array);
        return \array_keys($keys) === $keys;
    }

    public static function convertNestedObjects(mixed $value): array
    {
        try {
            $encoded = \json_encode($value, \JSON_THROW_ON_ERROR);
            $decoded = \json_decode($encoded, true, 512, \JSON_THROW_ON_ERROR);
            return self::wrap($decoded);
        } catch (JsonException) {
            return [];
        }
    }
}
