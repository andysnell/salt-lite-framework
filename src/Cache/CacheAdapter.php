<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\Cache\Exception\CacheWriteFailed;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Adapts a PSR-16 cache instance to our Cache interface
 *
 * @link https://www.php-fig.org/psr/psr-16/
 */
#[Internal]
class CacheAdapter implements Cache
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
    public function set(\Stringable|string $key, Ttl $ttl, mixed $value): bool
    {
        return $this->cache->set(self::normalize($key), $value, self::ttl($ttl));
    }

    #[\Override]
    public function setMultiple(Ttl $ttl, iterable $values): bool
    {
        return $this->cache->setMultiple((static function (iterable $values): \Generator {
            foreach ($values as $key => $value) {
                yield self::normalize($key) => $value;
            }
        })($values), self::ttl($ttl));
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
    public function remember(
        \Stringable|string $key,
        Ttl $ttl,
        callable $callback,
        bool $force_refresh = false,
    ): mixed {
        $key = self::normalize($key);
        $value = $force_refresh ? null : $this->cache->get($key);
        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        if ($value !== null) {
            $this->cache->set($key, $value, self::ttl($ttl)) || throw new CacheWriteFailed('set: ' . $key);
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

    private static function ttl(Ttl $ttl): int|null
    {
        return $ttl->seconds === Ttl::max()->seconds ? null : (int)$ttl->seconds;
    }
}
