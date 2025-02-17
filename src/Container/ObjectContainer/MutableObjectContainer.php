<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ObjectContainer;

use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\HasInvokingContainerBehavior;
use PhoneBurner\SaltLite\Framework\Domain\Arrayable;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\HasMutableContainerArrayableBehavior;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\HasMutableContainerArrayAccessBehavior;
use PhoneBurner\SaltLite\Framework\Util\Collections\MapCollection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template T of object
 * @implements ObjectContainer<T>
 */
#[Contract]
class MutableObjectContainer implements ObjectContainer, MutableContainer, Arrayable
{
    use HasInvokingContainerBehavior;
    use HasMutableContainerArrayAccessBehavior;
    use HasMutableContainerArrayableBehavior;

    /** @param array<string, T> $entries */
    public function __construct(protected array $entries = [])
    {
    }

    /**
     * @return T&object
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(\Stringable|string $id): object
    {
        return $this->entries[(string)$id] ?? throw new NotFound();
    }

    public function set(\Stringable|string $id, mixed $value): void
    {
        $this->entries[(string)$id] = $value;
    }

    public function unset(\Stringable|string $id): void
    {
        unset($this->entries[(string)$id]);
    }

    public function has(\Stringable|string $id): bool
    {
        return isset($this->entries[(string)$id]);
    }

    /**
     * @param array<string, T>|MapCollection<T> $map
     */
    public function replace(array|MapCollection $map): static
    {
        $this->entries = $map instanceof MapCollection ? $map->toArray() : $map;
        return $this;
    }

    public function clear(): void
    {
        $this->entries = [];
    }

    public function toArray(): array
    {
        return $this->entries;
    }
}
