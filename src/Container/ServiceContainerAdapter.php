<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\Domain\NullEntry;
use PhoneBurner\SaltLite\Framework\Container\Exception\CircularDependency;
use PhoneBurner\SaltLite\Framework\Container\Exception\ResolutionFailure;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ServiceFactory\Reference;
use PhoneBurner\SaltLite\Framework\Container\ServiceFactory\ServiceFactory;
use PhoneBurner\SaltLite\Framework\Logging\BufferLogger;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ServiceContainerAdapter implements ServiceContainer
{
    private array $deferred = [];

    private array $resolved = [];

    private array $definitions = [];

    private array $resolving = [];

    private string|null $top_level_id = '';

    private readonly ReflectionMethodAutoResolver $auto_resolver;

    public function __construct(
        private readonly App $app,
        private LoggerInterface $logger = new BufferLogger(),
    ) {
        $this->auto_resolver = new ReflectionMethodAutoResolver($this);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        // Write any buffered log entries to the new logger
        if ($this->logger instanceof BufferLogger) {
            $this->logger->copy($logger);
        }

        $this->logger = $logger;
    }

    public function has(string $id): bool
    {
        return isset($this->resolved[$id])
            || isset($this->definitions[$id])
            || isset($this->deferred[$id]);
    }

    public function get(string $id): mixed
    {
        $entry = $this->resolved[$id] ??= $this->resolve($id);

        return $entry !== NullEntry::None ? $entry : null;
    }

    private function resolve(string $id): mixed
    {
        \assert(Type::isClassString($id), \sprintf('Service "%s" must be a class string', $id));
        try {
            // handle deferred services
            if (\array_key_exists($id, $this->deferred)) {
                $this->registerProvider($this->deferred[$id]);
                if (isset($this->resolved[$id])) {
                    return $this->resolved[$id];
                }

                if (! isset($this->definitions[$id])) {
                    $message = \sprintf('Service Container: Deferred Service "%s" was not registered by its provider', $id);
                    $this->logger->error($message);
                    throw new ResolutionFailure($message);
                }
            }

            // set the top level id if it is not already set to track circular dependencies
            // then check to make sure we aren't in an unresolved circular dependency
            $this->top_level_id ??= null;
            if (isset($this->resolving[$id])) {
                throw new CircularDependency($this->top_level_id, $id);
            }
            $this->resolving[$id] = true;

            return $this->factory($id) ?? $this->make($id);
        } catch (\Throwable $e) {
            throw $e instanceof ContainerExceptionInterface ? $e : new ResolutionFailure(previous: $e);
        } finally {
            // if we have the original outermost id, we can clear it
            if ($this->top_level_id === $id) {
                $this->top_level_id = null;
            }
            unset($this->resolving[$id]);
        }
    }

    /**
     * resolve a service from a defintion
     */
    private function factory(string $id): mixed
    {
        $definition = $this->definitions[$id] ?? null;
        if ($definition === null) {
            return null;
        }

        if (\is_callable($definition)) {
            return $definition($this->app);
        }

        throw new ResolutionFailure(\sprintf('Service Container: Unable to execute definition for service "%s"', $id));
    }

    public function set(string $id, mixed $value): void
    {
        // We need to handle deferred services that are being set directly, and
        // ensure that all the other services deferred in that provider will be registered
        if (isset($this->deferred[$id])) {
            $this->registerProvider($this->deferred[$id]);
        }

        // clear out any existing definitions and resolved values for the id;
        unset($this->resolved[$id], $this->definitions[$id]);

        // set the value based on the type with special handling for null
        match (true) {
            $value instanceof ServiceFactory, $value instanceof \Closure => $this->definitions[$id] = $value,
            $value === null => $this->resolved[$id] = NullEntry::None,
            default => $this->resolved[$id] = $value,
        };
    }

    public function bind(string $interface, string $implementation): void
    {
        $this->set($interface, static fn(ContainerInterface $container): mixed => $container->get($implementation));
    }

    /**
     * @param DeferrableServiceProvider|class-string<DeferrableServiceProvider> $service_provider
     */
    public function defer(string $id, DeferrableServiceProvider|string $service_provider): void
    {
        $this->deferred[$id] = $service_provider;
    }

    /**
     * Note: This method is not part of the PSR-11 interface, and will provide a
     * new instance each time it is called, which is different behavior from the
     * get() method. It's probably better to create a factory class to create the
     * instances you need and resolve that from the container, rather than relying
     * on auto-wiring and a more open type-signature.
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T&object
     */
    public function make(string $class, OverrideCollection|null $overrides = null): object
    {
        $reflection = new \ReflectionClass($class);

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $class();
        }

        $arguments = $this->auto_resolver->getArgumentsFor($constructor, $overrides);
        if ($arguments === []) {
            return new $class();
        }

        return $reflection->newInstanceArgs($arguments);
    }

    #[\Override]
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
        $arguments = $this->auto_resolver->getArgumentsFor($reflection_method, $overrides);

        return $reflection_method->invokeArgs($object, $arguments);
    }

    /**
     * @param ServiceProvider|class-string<ServiceProvider> $provider
     */
    public function registerProvider(ServiceProvider|string $provider): void
    {
        \assert(\is_a($provider, ServiceProvider::class, true));

        // Remove deferred services from the list so we don't accidentally re-register them
        if (\is_a($provider, DeferrableServiceProvider::class, true)) {
            foreach ($provider::provides() as $provided) {
                unset($this->deferred[$provided]);
            }
        }

        foreach ($provider::bind() as $abstract => $concrete) {
            $this->set($abstract, new Reference($concrete));
        }
        $provider::register($this->app);
    }

    /**
     * @param DeferrableServiceProvider|class-string<ServiceProvider> $provider
     */
    public function deferProvider(DeferrableServiceProvider|string $provider): void
    {
        \assert(\is_a($provider, DeferrableServiceProvider::class, true));
        foreach ($provider::provides() as $provided) {
            $this->defer($provided, $provider);
        }
    }
}
