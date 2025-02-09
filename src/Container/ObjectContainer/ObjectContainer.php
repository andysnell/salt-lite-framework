<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ObjectContainer;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @template T of object
 * @extends \IteratorAggregate<string, T>
 * @extends \ArrayAccess<string, T>
 */
interface ObjectContainer extends ContainerInterface, \Countable, \IteratorAggregate, \ArrayAccess
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
    public function identifiers(): array;
}
