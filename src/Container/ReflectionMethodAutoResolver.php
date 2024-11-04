<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Container\Exception\OverriddenArgumentNotSet;
use PhoneBurner\SaltLite\Framework\Container\Exception\UnableToAutoResolveParameterException;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;

#[Internal]
class ReflectionMethodAutoResolver
{
    public static function usingContainer(MutableContainer $container): self
    {
        return new self($container);
    }

    public function __construct(
        private readonly MutableContainer $container,
        private readonly array $auto_resolve_block_list = [],
    ) {
    }

    /**
     * @param Override|array<Override>|OverrideCollection $overrides
     */
    public function getArgumentsFor(\ReflectionMethod $method, Override|OverrideCollection|array $overrides = []): array
    {
        if (! $overrides instanceof OverrideCollection) {
            $overrides = new OverrideCollection($overrides);
        }

        return \array_map(function (\ReflectionParameter $parameter) use ($overrides) {
            return $this->resolve($parameter, $overrides);
        }, $method->getParameters());
    }

    /**
     * @return mixed
     * @throws UnableToAutoResolveParameterException
     * @throws OverriddenArgumentNotSet
     */
    private function resolve(\ReflectionParameter $parameter, OverrideCollection $overrides)
    {
        if ($overrides->hasArgumentInPosition($parameter->getPosition())) {
            return $overrides->getArgumentInPosition($parameter->getPosition());
        }

        if ($overrides->hasArgumentByName($parameter->getName())) {
            return $overrides->getArgumentByName($parameter->getName());
        }

        $type = $parameter->getType();
        if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
            /** @var class-string $class */
            $class = $type->getName();
            return $this->resolveTypeHintedParameter($parameter, new \ReflectionClass($class), $overrides);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $class = $parameter->getDeclaringClass();
        $name = $class ? $class->getName() : '';
        throw new UnableToAutoResolveParameterException(
            'Unable to resolve value for parameter `$' . $parameter->getName() . '`' .
            ' for class `' . $name . '`',
        );
    }

    /**
     * @template T of object
     * @param \ReflectionClass<T> $hint
     * @return mixed
     */
    private function resolveTypeHintedParameter(
        \ReflectionParameter $parameter,
        \ReflectionClass $hint,
        OverrideCollection $overrides,
    ) {
        if ($overrides->hasArgumentByTypeHint($hint->getName())) {
            return $overrides->getArgumentByTypeHint($hint->getName());
        }

        if ($this->container->has($hint->getName())) {
            return $this->container->get($hint->getName());
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // Limit overrides to first level resolving
        return $this->autoResolveTypeHint($hint);
    }

    /**
     * @template T of object
     * @param \ReflectionClass<T> $hint
     * @return T
     * @throws UnableToAutoResolveParameterException
     */
    private function autoResolveTypeHint(\ReflectionClass $hint): object
    {
        if ($this->isClassAutoResolveBlocked($hint)) {
            throw new UnableToAutoResolveParameterException(
                $hint->getName() . ' can not be auto resolved from container due to block list.',
            );
        }

        return $this->container->get($hint->getName());
    }

    /**
     * @template T of object
     * @param \ReflectionClass<T> $class
     */
    private function isClassAutoResolveBlocked(\ReflectionClass $class): bool
    {
        foreach ($this->auto_resolve_block_list as $block_listed) {
            if ($class->getName() === $block_listed || $class->isSubclassOf($block_listed)) {
                return true;
            }
        }

        return false;
    }
}
