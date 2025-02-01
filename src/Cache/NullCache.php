<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;

final class NullCache extends InMemoryCache
{
    public function __construct()
    {
    }

    #[\Override]
    public function get(\Stringable|string $key): null
    {
        return null;
    }

    #[\Override]
    public function getMultiple(iterable $keys): iterable
    {
        return [];
    }

    #[\Override]
    public function set(\Stringable|string $key, mixed $value, Ttl $ttl = new Ttl()): bool
    {
        return true;
    }

    #[\Override]
    public function setMultiple(iterable $values, Ttl $ttl = new Ttl()): bool
    {
        return true;
    }

    #[\Override]
    public function delete(\Stringable|string $key): bool
    {
        return true;
    }

    #[\Override]
    public function deleteMultiple(iterable $keys): bool
    {
        return true;
    }

    #[\Override]
    public function remember(
        \Stringable|string $key,
        callable $callback,
        Ttl $ttl = new Ttl(),
        bool $force_refresh = false,
    ): mixed {
        return $callback();
    }

    #[\Override]
    public function forget(\Stringable|string $key): null
    {
        return null;
    }
}
