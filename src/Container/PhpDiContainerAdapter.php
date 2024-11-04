<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use DI\Container as PhpDiContainer;
use DI\ContainerBuilder;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

#[Internal]
class PhpDiContainerAdapter implements MutableContainer
{
    private readonly PhpDiContainer $php_di;

    public function __construct(
        PhpDiContainer|null $container = null,
    ) {
        $this->php_di = $container ?? (new ContainerBuilder())
            ->useAutowiring(false)
            ->wrapContainer($this)
            ->build();
    }

    /**
     * @inheritdoc
     * @template T of object
     * @param class-string<T> $id
     * @phpstan-ignore-next-line This non-generic method intentionally annotates generics
     */
    #[\Override]
    public function get(string $id): mixed
    {
        try {
            if ($this->php_di->has($id)) {
                return $this->php_di->get($id);
            }
        } catch (NotFoundExceptionInterface) {
            // intentional suppression to allow for auto-resolving
        }

        // Fallback Autowiring
        $value = $this->make($id);
        $this->set($id, $value);
        return $value;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @inheritdoc
     */
    #[\Override]
    public function has(string $id): bool
    {
        return $this->php_di->has($id);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function set(string $id, $value): void
    {
        $this->php_di->set($id, $value);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function make(string $class, $overrides = []): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return new $class();
        }

        return $reflection->newInstanceArgs($this->resolveArguments($constructor, $overrides));
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function call(object $object, string $method, $overrides = []): mixed
    {
        $reflection_method = (new \ReflectionObject($object))->getMethod($method);
        $resolved_arguments = $this->resolveArguments($reflection_method, $overrides);
        return $reflection_method->invokeArgs($object, $resolved_arguments);
    }

    /**
     * @param Override|Override[]|OverrideCollection $overrides
     */
    private function resolveArguments(\ReflectionMethod $method, Override|OverrideCollection|array $overrides): array
    {
        return (new ReflectionMethodAutoResolver($this))->getArgumentsFor($method, $overrides);
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function bind(string $interface, string $implementation): void
    {
        $this->set($interface, static fn(ContainerInterface $container): mixed => $container->get($implementation));
    }
}
