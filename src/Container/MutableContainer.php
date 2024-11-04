<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use Psr\Container\ContainerInterface;

#[Contract]
interface MutableContainer extends ContainerInterface
{
    /**
     * Add a new element to the container.
     *
     * @param string $id Identifier of the entry to add.
     * @param mixed $value Either the instance of the class or a Closure which creates an instance.
     */
    public function set(string $id, mixed $value): void;

    /**
     * Set the element used for the interface identifier to the container resolved
     * instance of the concrete implementation.
     *
     * @template T1 of object
     * @template T2 of T1
     * @param class-string<T1> $interface
     * @param class-string<T2> $implementation
     */
    public function bind(string $interface, string $implementation): void;

    /**
     * Create an object using the container for DI allowing for manual overrides.
     *
     * @template T of object
     * @param class-string<T> $class
     * @param Override|Override[]|OverrideCollection $overrides
     * @return T
     */
    public function make(string $class, Override|OverrideCollection|array $overrides = []): object;

    /**
     * Invoke a method on an object.
     *
     * @param Override|Override[]|OverrideCollection $overrides
     */
    public function call(object $object, string $method, Override|OverrideCollection|array $overrides = []): mixed;
}
