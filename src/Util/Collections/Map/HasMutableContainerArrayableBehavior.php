<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Collections\Map;

use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Domain\Arrayable;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\KeyValueStore;

/**
 * @phpstan-require-implements Arrayable
 * @phpstan-require-implements MutableContainer
 */
trait HasMutableContainerArrayableBehavior
{
    public function find(\Stringable|string $key): mixed
    {
        return $this->has($key) ? $this->get($key) : null;
    }

    public function contains(mixed $value, bool $strict = true): bool
    {
        return \in_array($value, $this->toArray(), $strict);
    }

    public function remember(\Stringable|string $key, callable $callback): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value);

        return $value;
    }

    public function forget(\Stringable|string $key): mixed
    {
        $value = $this->find($key);
        $this->unset($key);
        return $value;
    }

    public function map(callable $callback): KeyValueStore
    {
        return new KeyValueStore(\array_map($callback, $this->toArray(), $this->keys()));
    }

    public function filter(callable|null $callback = null): static
    {
        $this->replace(\array_filter($this->toArray(), $callback, \ARRAY_FILTER_USE_BOTH));
        return $this;
    }

    public function reject(callable $callback): static
    {
        return $this->filter(static fn(mixed $value, string $key): bool => ! $callback($value, $key));
    }

    public function all(callable $callback): bool
    {
        return \array_all($this->toArray(), $callback);
    }

    public function any(callable $callback): bool
    {
        return \array_any($this->toArray(), $callback);
    }

    public function count(): int
    {
        return \count($this->toArray());
    }

    public function keys(): array
    {
        return \array_keys($this->toArray());
    }

    public function getIterator(): \Generator
    {
        yield from $this->toArray();
    }

    public function isEmpty(): bool
    {
        return $this->toArray() === [];
    }
}
