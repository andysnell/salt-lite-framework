<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ObjectContainer;

use PhoneBurner\SaltLite\Framework\Container\InvokingContainer;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ReflectionMethodAutoResolver;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;

/**
 * @template T of object
 * @extends MutableObjectContainer<T>
 */
class InvokingObjectContainer extends MutableObjectContainer implements InvokingContainer
{
    public function call(
        object|string $object,
        string $method = '__invoke',
        OverrideCollection|null $overrides = null,
    ): mixed {
        if ($method === '__invoke' && ! \is_callable($object)) {
            throw new \UnexpectedValueException(\sprintf('Object of class "%s" is not invokable', $object::class));
        }

        \assert(Type::isClass($object));
        $object = \is_string($object) ? $this->get($object) : $object;
        $reflection_method = new \ReflectionClass($object)->getMethod($method);
        $arguments = new ReflectionMethodAutoResolver($this)->getArgumentsFor($reflection_method, $overrides);

        return $reflection_method->invokeArgs($object, $arguments);
    }
}
