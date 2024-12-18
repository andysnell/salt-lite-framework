<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheItemPoolProxy implements CacheItemPoolInterface
{
    public function __construct(private readonly CacheItemPoolInterface $cache_item_pool)
    {
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->cache_item_pool->getItem($key);
    }

    public function getItems(array $keys = []): iterable
    {
        return $this->cache_item_pool->getItems($keys);
    }

    public function hasItem(string $key): bool
    {
        return $this->cache_item_pool->hasItem($key);
    }

    public function clear(): bool
    {
        return $this->cache_item_pool->clear();
    }

    public function deleteItem(string $key): bool
    {
        return $this->cache_item_pool->deleteItem($key);
    }

    public function deleteItems(array $keys): bool
    {
        return $this->cache_item_pool->deleteItems($keys);
    }

    public function save(CacheItemInterface $item): bool
    {
        return $this->cache_item_pool->save($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->cache_item_pool->saveDeferred($item);
    }

    public function commit(): bool
    {
        return $this->cache_item_pool->commit();
    }
}
