<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ObjectContainer;

use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\HasInvokingContainerBehavior;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template T of object
 * @implements ObjectContainer<T>
 */
#[Contract]
class MutableObjectContainer implements ObjectContainer, MutableContainer
{
    use HasInvokingContainerBehavior;

    /** @param array<string, T> $entries */
    public function __construct(protected array $entries = [])
    {
    }

    /**
     * @return T&object
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(string $id): object
    {
        return $this->entries[$id] ?? throw new NotFound();
    }

    public function set(string $id, mixed $value): void
    {
        $this->entries[$id] = $value;
    }

    public function unset(string $id): void
    {
        unset($this->entries[$id]);
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    /**
     * @return array<string>
     */
    public function keys(): array
    {
        return \array_keys($this->entries);
    }

    public function getIterator(): \Generator
    {
        yield from $this->entries;
    }

    public function count(): int
    {
        return \count($this->entries);
    }

    public function offsetExists(mixed $offset): bool
    {
        return \is_string($offset) && $this->has($offset);
    }

    /**
     * @return T&object
     */
    public function offsetGet(mixed $offset): object
    {
        \is_string($offset) || throw new \InvalidArgumentException('Offset must be a string');
        return $this->entries[$offset] ?? throw new NotFound();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        \is_string($offset) || throw new \InvalidArgumentException('Offset must be a string');
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        \is_string($offset) || throw new \InvalidArgumentException('Offset must be a string');
        unset($this->entries[$offset]);
    }
}
