<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Collections;

use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Domain\Arrayable;

/**
 * @template TValue
 * @extends \ArrayAccess<string, TValue>
 * @extends \IteratorAggregate<string, TValue>
 */
interface MapCollection extends \ArrayAccess, \Countable, \IteratorAggregate, MutableContainer, Arrayable
{
    public function has(\Stringable|string $id): bool;

    /**
     * @return TValue
     * @throws NotFound
     */
    public function get(\Stringable|string $id): mixed;

    /**
     * @return TValue|null
     */
    public function find(\Stringable|string $id): mixed;

    /**
     * Implementations MUST throw an exception if the value is not of the expected type
     *
     * @param TValue $value
     */
    public function set(\Stringable|string $id, mixed $value): void;

    public function unset(\Stringable|string $id): void;

    /**
     * Replace all the values of this map with the provided map or array
     *
     * @param MapCollection<TValue>|array<string, TValue> $map
     */
    public function replace(self|array $map): static;

    /**
     * @param callable(): TValue $callback
     */
    public function remember(\Stringable|string $id, callable $callback): mixed;

    public function forget(\Stringable|string $id): mixed;

    /**
     * @param bool $strict if true, the comparison will be by identity, otherwise by equality
     */
    public function contains(mixed $value, bool $strict = false): bool;

    /**
     * @template T
     * @param callable(TValue $value): T $callback
     * @return MapCollection<T> mapping does not necessarily preserve the type of the map implementation
     */
    public function map(callable $callback): self;

    /**
     * The filter method filters the map using a callback function. The callback function
     * should return true if the current value passes the filter and false if it does not.
     * If null is passed, the filter method will remove all values that are equal to false.
     *
     * @param null|callable(TValue $value, string $id): bool $callback
     * @return static Filtering must preserve the map implementation and may mutate the map
     */
    public function filter(callable|null $callback = null): static;

    /**
     * The reject method is the opposite of the filter method. It removes all elements
     * for which the callback returns true.
     *
     * @param callable(TValue $value, string $id): bool $callback
     * @return static Filtering must preserve the map implementation may mutate the map
     */
    public function reject(callable $callback): static;

    /**
     * Returns true if the callback returns true for every element in the map.
     * If any element returns false, the method will return false and not be called
     * for the remaining elements.
     *
     * Note, this method also serves the same purpose as an "each" or "every" method
     * as long as the callback does not return false.
     *
     * @param callable(TValue $value, string $id): bool $callback
     */
    public function all(callable $callback): bool;

    /**
     * Returns true if the callback returns true for any element in the map.
     *
     * @param callable(TValue $value, string $id): bool $callback
     */
    public function any(callable $callback): bool;

    public function clear(): void;

    public function keys(): array;

    public function isEmpty(): bool;
}
