<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

/**
 * This is the primary interface for interacting with the cache, and should be
 * the default interface to inject into your classes for most use cases.
 *
 * This is not a PSR-16 implementation, rather this interface defines is a
 * simplified version of the PSR-16 cache interface intended to cover the vast
 * majority of our use cases, without exposing the full PSR-16 interface. This
 * allows us to define methods with narrower parameter and return types, as well
 * as enforce that all items are cached with a TTL.
 *
 * Following the recommendation in the PSR-16 documentation, we do not implement
 * as "has" method, which is subject to race condition errors. Instead, you can
 * check that the return of get() is not null.
 */
#[Contract]
interface Cache extends AppendOnlyCache
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
    public function set(string|\Stringable $key, mixed $value, Ttl $ttl = new Ttl()): bool;

    /**
     * Set multiple items in the cache in a single operation
     *
     * @param iterable<string|\Stringable, mixed> $values A list of key => value pairs for a multiple-set operation.
     * @return bool True on success and false on failure.
     */
    public function setMultiple(iterable $values, Ttl $ttl = new Ttl()): bool;

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
    public function remember(
        string|\Stringable $key,
        callable $callback,
        Ttl $ttl = new Ttl(),
        bool $force_refresh = false,
    ): mixed;

    /**
     * Deletes a key from the cache, returning the value if it existed, otherwise
     * returns null
     */
    public function forget(string|\Stringable $key): mixed;
}
