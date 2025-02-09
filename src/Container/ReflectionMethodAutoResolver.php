<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Container\Exception\UnableToAutoResolveParameter;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideType;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\ParameterOverride;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use Psr\Container\ContainerInterface;

#[Internal]
class ReflectionMethodAutoResolver
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function getArgumentsFor(\ReflectionMethod $method, OverrideCollection|null $overrides = null): array
    {
        $parameters = $method->getParameters();
        return match (true) {
            $parameters === [] => [],
            $overrides instanceof OverrideCollection => \array_map(
                fn(\ReflectionParameter $parameter): mixed => $this->resolveWithOverrides($parameter, $overrides),
                $parameters,
            ),
            default => \array_map($this->resolve(...), $parameters),
        };
    }

    private function resolve(\ReflectionParameter $parameter): mixed
    {
        $class = self::resolveClassNameFromType($parameter->getType());

        return match (true) {
            $class === null => match ($parameter->isDefaultValueAvailable()) {
                true => $parameter->getDefaultValue(),
                false => throw new UnableToAutoResolveParameter($parameter),
            },
            $this->container->has($class) => $this->container->get($class),
            $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
            default => $this->container->get($class), // Limit overrides to first level resolution
        };
    }

    private function resolveWithOverrides(\ReflectionParameter $parameter, OverrideCollection $overrides): mixed
    {
        $position_override = $overrides->find(OverrideType::Position, $parameter->getPosition());
        $name_override = $overrides->find(OverrideType::Name, $parameter->getName());
        $class = self::resolveClassNameFromType($parameter->getType());

        return match (true) {
            $position_override instanceof ParameterOverride => $position_override->value(),
            $name_override instanceof ParameterOverride => $name_override->value(),
            $class === null => match ($parameter->isDefaultValueAvailable()) {
                true => $parameter->getDefaultValue(),
                false => throw new UnableToAutoResolveParameter($parameter),
            },
            $overrides->has(OverrideType::Hint, $class) => $overrides->find(OverrideType::Hint, $class)?->value(),
            $this->container->has($class) => $this->container->get($class),
            $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
            default => $this->container->get($class), // Limit overrides to first level resolution
        };
    }

    private static function resolveClassNameFromType(\ReflectionType|null $type): string|null
    {
        return match (true) {
            ! $type instanceof \ReflectionNamedType,
            $type->isBuiltin(), // strings, int, etc
            ! Type::isClassString($type->getName()) => null,
            default => $type->getName(),
        };
    }
}
