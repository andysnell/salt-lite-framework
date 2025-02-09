<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer;

use PhoneBurner\SaltLite\Framework\Container\Exception\UnableToAutoResolveParameter;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideType;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;

#[Internal]
class ReflectionMethodAutoResolver
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly OverrideCollection|null $overrides = null,
    ) {
    }

    public function __invoke(\ReflectionParameter $parameter): mixed
    {
        $type = self::type($parameter->getType());

        return match (true) {
            // 1) Override exists for specific argument position
            $this->overrides?->has(
                OverrideType::Position,
                $parameter->getPosition(),
            ) => $this->overrides->find(OverrideType::Position, $parameter->getPosition())?->value(),

            // 2) Override exists for specific parameter name
            $this->overrides?->has(
                OverrideType::Name,
                $parameter->getName(),
            ) => $this->overrides->find(OverrideType::Name, $parameter->getName())?->value(),

            // 3) The type isn't a class name, try to return the default, otherwise, fail.
            $type === null => match ($parameter->isDefaultValueAvailable()) {
                true => $parameter->getDefaultValue(),
                false => throw new UnableToAutoResolveParameter($parameter),
            },

            // 4) Override exists for parameter type, which was validated in previous match arm
            $this->overrides?->has(
                OverrideType::Hint,
                $type->getName(),
            ) => $this->overrides->find(OverrideType::Hint, $type->getName())?->value(),

            // 5) Does the container have an *explicit* entry for the type? That is,
            //    we don't want `has()` to return true if the container might be
            //    able to autowire it.
            $this->container instanceof ServiceContainer
                ? $this->container->has($type->getName(), true)
                : $this->container->has($type->getName()) => $this->container->get($type->getName()),

            // 6) If there's a default value, we want to use that instead of trying to
            //    autowire something up from the container. This wasn't such a problem
            //    before PHP added "new in initializer" functionality.
            $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),

            // 7) Finally, the fallback here is to just ask the container to try
            //    to get the entry. If the container doesn't autowire undefined
            //    class entries, this will end up throwing the expected `NotFound`
            //    exception. Note that overrides are limited to first level resolution.
            default => $this->container->get($type->getName()),
        };
    }

    private static function type(\ReflectionType|null $type): \ReflectionNamedType|null
    {
        return match (true) {
            ! $type instanceof \ReflectionNamedType,
            $type->isBuiltin(), // strings, int, etc
            \in_array($type->getName(), ['parent', 'self', 'static'], true) => null,
            default => $type,
        };
    }
}
