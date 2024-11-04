<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\Cache\Exception\CacheWriteFailed;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;

class AppendOnlyCacheAdapter implements AppendOnlyCache
{
    private readonly CacheInterface $cache;

    public function __construct(CacheItemPoolInterface|CacheInterface $cache)
    {
        $this->cache = $cache instanceof CacheInterface ? $cache : new Psr16Cache($cache);
    }

    #[\Override]
    public function get(\Stringable|string $key): mixed
    {
        return $this->cache->get(self::normalize($key));
    }

    #[\Override]
    public function getMultiple(iterable $keys): iterable
    {
        return $this->cache->getMultiple((static function (iterable $keys): \Generator {
            foreach ($keys as $key) {
                yield self::normalize($key);
            }
        })($keys));
    }

    #[\Override]
    public function set(\Stringable|string $key, mixed $value): bool
    {
        return $this->cache->set(self::normalize($key), $value);
    }

    #[\Override]
    public function setMultiple(iterable $values): bool
    {
        return $this->cache->setMultiple((static function (iterable $values): \Generator {
            foreach ($values as $key => $value) {
                yield self::normalize($key) => $value;
            }
        })($values));
    }

    #[\Override]
    public function delete(\Stringable|string $key): bool
    {
        return $this->cache->delete(self::normalize($key));
    }

    #[\Override]
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->cache->deleteMultiple((static function (iterable $keys): \Generator {
            foreach ($keys as $key) {
                yield self::normalize($key);
            }
        })($keys));
    }

    #[\Override]
    public function remember(\Stringable|string $key, callable $callback): mixed
    {
        $key = self::normalize($key);
        $value = $this->cache->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        if ($value !== null) {
            $this->cache->set($key, $value) || throw new CacheWriteFailed('set: ' . $key);
        }

        return $value;
    }

    #[\Override]
    public function forget(\Stringable|string $key): mixed
    {
        $key = self::normalize($key);
        $value = $this->cache->get($key);
        if ($value !== null) {
            $this->cache->delete($key) || throw new CacheWriteFailed('delete: ' . $key);
        }

        return $value;
    }

    public static function normalize(\Stringable|string $key): string
    {
        return $key instanceof CacheKey ? $key->normalized : CacheKey::make($key)->normalized;
    }
}
