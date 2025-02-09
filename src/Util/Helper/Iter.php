<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use PhoneBurner\SaltLite\Framework\Domain\Arrayable;

/**
 * Helper class for working with iterators.
 *
 * @see \PhoneBurner\SaltLite\Framework\Util\Helper\Arr for working with arrays.
 */
abstract readonly class Iter
{
    /**
     * The `iterable` pseudotype is the union of `array|Traversable`, and can be
     * used for both parameter and return typing; however, almost all the
     * PHP functions for working with iterable things will only accept `array`
     * or a `Traversable` object. We commonly need one or the other, and by type
     * hinting on `iterable`, we don't know at runtime what we are working with.
     * This helper method takes any iterable and returns an `Iterator`.
     * This also works with any class that implements Arrayable. If an object is
     * an instance of both `Traversable` and `Arrayable`, the method returns the
     * object like other `Traversable` objects.
     *
     * @template T
     * @param Arrayable|iterable<T> $value
     * @return \Iterator<T>
     */
    final public static function cast(Arrayable|iterable $value): \Iterator
    {
        return match (true) {
            \is_array($value) => new \ArrayIterator($value),
            $value instanceof \Iterator => $value,
            $value instanceof \Traversable => new \IteratorIterator($value),
            $value instanceof Arrayable => new \ArrayIterator($value->toArray()),
        };
    }

    final public static function first(iterable $iter): mixed
    {
        foreach ($iter as $value) {
            return $value;
        }

        return null;
    }

    final public static function last(iterable $iter): mixed
    {
        $last = null;
        foreach ($iter as $value) {
            $last = $value;
        }

        return $last;
    }

    /**
     * Maps a callback on each element of an iterable, where the first parameter
     * of the callback is the value and the second parameter is the key.
     *
     * @param callable(mixed, int|string): mixed $callback
     */
    final public static function map(callable $callback, iterable $iter): \Generator
    {
        foreach ($iter as $key => $value) {
            yield $key => $callback($value, $key);
        }
    }

    /**
     * Maps an iterable to an array via a callback
     *
     * @param callable(mixed): mixed $callback
     */
    final public static function amap(callable $callback, iterable $iter): array
    {
        $result = [];
        foreach ($iter as $key => $value) {
            $result[$key] = $callback($value);
        }
        return $result;
    }
}
