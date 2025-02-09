<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use Psr\Container\ContainerInterface;

/**
 * Defines containers that know how to call methods on objects they contain with
 * the ability to override parameters.
 */
interface InvokingContainer extends ContainerInterface
{
    /**
     * Call a method on an object resolved from this container instance. If a
     * method is not provided, the object will be invoked as a callable. If a
     * class-string is passed instead of an object, the container will attempt to
     * resolve the object from itself before calling the method on the instance.
     *
     * @param object|class-string $object
     */
    public function call(
        object|string $object,
        string $method = '__invoke',
        OverrideCollection|null $overrides = null,
    ): mixed;
}
