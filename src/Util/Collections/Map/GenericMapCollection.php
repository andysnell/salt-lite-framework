<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Collections\Map;

use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\HasMutableContainerArrayAccessBehavior;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\KeyValueStore;
use PhoneBurner\SaltLite\Framework\Util\Collections\MapCollection;

/**
 * @template TValue
 * @implements MapCollection<TValue>
 */
abstract class GenericMapCollection implements MapCollection
{
    use HasMutableContainerArrayAccessBehavior;

    protected array $data = [];

    public function has(\Stringable|string $id): bool
    {
        return \array_key_exists((string)$id, $this->data);
    }

    public function get(\Stringable|string $id): mixed
    {
        return $this->has($id) ? $this->data[(string)$id] : throw new NotFound();
    }

    public function set(\Stringable|string $id, mixed $value): void
    {
        $this->data[(string)$id] = $value;
    }

    public function unset(\Stringable|string $id): void
    {
        unset($this->data[(string)$id]);
    }

    public function replace(MapCollection|array $map): static
    {
        $this->data = $map instanceof MapCollection ? $map->toArray() : $map;
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function __serialize(): array
    {
        return $this->data;
    }

    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }

    public function find(\Stringable|string $id): mixed
    {
        return $this->data[(string)$id] ?? null;
    }

    public function contains(mixed $value, bool $strict = true): bool
    {
        return \in_array($value, $this->data, $strict);
    }

    public function remember(\Stringable|string $id, callable $callback): mixed
    {
        if (\array_key_exists((string)$id, $this->data)) {
            return $this->data[(string)$id];
        }

        $value = $callback();
        $this->set($id, $value);

        return $value;
    }

    public function forget(\Stringable|string $id): mixed
    {
        $value = $this->find($id);
        $this->unset($id);
        return $value;
    }

    public function map(callable $callback): KeyValueStore
    {
        return new KeyValueStore(\array_map($callback, $this->data));
    }

    public function filter(callable|null $callback = null): static
    {
        $this->replace(\array_filter($this->data, $callback, \ARRAY_FILTER_USE_BOTH));
        return $this;
    }

    public function reject(callable $callback): static
    {
        return $this->filter(static fn(mixed $value, string $id): bool => ! $callback($value, $id));
    }

    public function all(callable $callback): bool
    {
        return \array_all($this->data, $callback);
    }

    public function any(callable $callback): bool
    {
        return \array_any($this->data, $callback);
    }

    public function count(): int
    {
        return \count($this->data);
    }

    public function keys(): array
    {
        return \array_keys($this->data);
    }

    public function getIterator(): \Generator
    {
        yield from $this->data;
    }

    public function isEmpty(): bool
    {
        return $this->data === [];
    }
}
