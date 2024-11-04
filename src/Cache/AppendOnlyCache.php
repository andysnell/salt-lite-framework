<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface AppendOnlyCache
{
    /**
     * Retrieve an item from the cache by key. Use this method to also check if
     * an item exists in the cache, e.g. in place of `has()`.
     */
    public function get(string|\Stringable $key): mixed;

    /**
     * Get multiple items from the cache in a single operation
     *
     * @param iterable<string|\Stringable> $keys
     * @return iterable<string, mixed> Will return an array of key => value pairs,
     * returning null for keys that do not exist. The array will be indexed by the
     * normalized form of the keys passed in (necessary to support stringable objects).
     */
    public function getMultiple(iterable $keys): iterable;

    /**
     * Store an item in the cache for a given number of seconds.
     */
    public function set(string|\Stringable $key, mixed $value): bool;

    /**
     * Set multiple items in the cache in a single operation
     *
     * @param iterable<string|\Stringable, mixed> $values A list of key => value pairs for a multiple-set operation.
     * @return bool True on success and false on failure.
     */
    public function setMultiple(iterable $values): bool;

    /**
     * Remove an item from the cache.
     */
    public function delete(string|\Stringable $key): bool;

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string|\Stringable> $keys A list of string-based keys to be deleted.
     * @return bool True if the items were successfully removed. False if there was an error.
     */
    public function deleteMultiple(iterable $keys): bool;

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function remember(string|\Stringable $key, callable $callback): mixed;

    /**
     * Deletes a key from the cache, returning the value if it existed, otherwise
     * returns null
     */
    public function forget(string|\Stringable $key): mixed;
}
