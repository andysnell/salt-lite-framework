<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ObjectContainer;

use PhoneBurner\SaltLite\Framework\Container\InvokingContainer;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template T of object
 * @extends \IteratorAggregate<string, T>
 * @extends \ArrayAccess<string, T>
 */
#[Contract]
interface ObjectContainer extends InvokingContainer, \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @return T&object
     * @throws NotFoundExceptionInterface No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get(string $id): object;

    public function has(string $id): bool;

    /**
     * @return array<string>
     */
    public function keys(): array;
}
