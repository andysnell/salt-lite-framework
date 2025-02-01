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
}
