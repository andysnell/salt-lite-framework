<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Container\Exception\CircularDependency;
use PhoneBurner\SaltLite\Framework\Container\Exception\InvalidServiceProvider;
use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Container\Exception\ResolutionFailure;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory\BindingServiceFactory;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory\CallableServiceFactory;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\BufferLogger;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use Psr\Container\ContainerExceptionInterface;
use Psr\Log\LoggerInterface;

#[Internal]
class ServiceContainerAdapter implements ServiceContainer
{
    use HasInvokingContainerBehavior;

    /**
     * @var array<class-string<DeferrableServiceProvider>, list<class-string>>
     */
    private array $deferred_providers = [];

    /**
     * @var array<class-string, class-string<DeferrableServiceProvider>>
     */
    private array $deferred = [];

    /**
     * @var array<class-string, object>
     */
    private array $resolved = [];

    /**
     * @var array<class-string, ServiceFactory>
     */
    private array $factories = [];

    /**
     * @var array<class-string, true>
     */
    private array $resolving = [];

    private string|null $outer_id = '';

    private readonly \Closure $auto_resolver_callback;

    public function __construct(
        private readonly App $app,
        private LoggerInterface $logger = new BufferLogger(),
    ) {
        $this->auto_resolver_callback = new ReflectionMethodAutoResolver($this)(...);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        // Write any buffered log entries to the new logger
        if ($this->logger instanceof BufferLogger) {
            $this->logger->copy($logger);
        }

        $this->logger = $logger;
    }

    /**
     * Returns true if :
     *  1) We already have a resolved entry for the $id
     *  2) We have a service factory that can resolve the entry
     *  3) A deferred service provider that can register an entry or service factory
     *  4) The $id string is a valid class-string for a class that we could potentially
     *     autowire, i.e., it is not an interface, trait, or abstract class.
     */
    public function has(\Stringable|string $id, bool $strict = false): bool
    {
        $id = (string)$id;
        return isset($this->resolved[$id])
            || isset($this->factories[$id])
            || isset($this->deferred[$id])
            || ($strict === false && \class_exists($id) && new \ReflectionClass($id)->isInstantiable());
    }

    public function get(\Stringable|string $id): object
    {
        $id = (string)$id;
        \assert(Type::isClassString($id));
        return $this->resolved[$id] ??= $this->resolve($id);
    }

    public function set(\Stringable|string $id, mixed $value): void
    {
        $id = (string)$id;
        \assert(Type::isClassString($id));

        if (! \is_object($value)) {
            throw new \InvalidArgumentException('ServiceContainer may contain only objects and object factories');
        }

        // We need to handle deferred services that are being set directly, and
        // ensure that all the other services deferred in that provider will be registered
        if (isset($this->deferred[$id])) {
            $this->register($this->deferred[$id]);
        }

        // clear out any existing definitions and resolved values for the id;
        unset($this->resolved[$id], $this->factories[$id]);

        // set the value based on the type with special handling for null
        match (true) {
            $value instanceof ServiceFactory => $this->factories[$id] = $value,
            $value instanceof \Closure => $this->factories[$id] = new CallableServiceFactory($value),
            default => $this->resolved[$id] = $value,
        };
    }

    public function unset(\Stringable|string $id): void
    {
        $id = (string)$id;
        unset($this->resolved[$id], $this->factories[$id]);
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
        $class_reflection = new \ReflectionClass($class);
        return match ($class_reflection->isInstantiable()) {
            true => $class_reflection->newInstanceArgs(\array_map(
                new ReflectionMethodAutoResolver($this, $overrides)(...),
                $class_reflection->getConstructor()?->getParameters() ?? [],
            )),
            false => throw new NotFound($class),
        };
    }

    /**
     * @param ServiceProvider|class-string<ServiceProvider> $service_provider
     */
    public function register(ServiceProvider|string $service_provider): null
    {
        if (! \is_a($service_provider, ServiceProvider::class, true)) {
            throw new InvalidServiceProvider($service_provider);
        }

        // since service providers must be static, we want to just use the class name
        $service_provider = \is_object($service_provider) ? $service_provider::class : $service_provider;

        // Remove deferred services from the list so we don't accidentally re-register them
        if (\array_key_exists($service_provider, $this->deferred_providers)) {
            foreach ($this->deferred_providers[$service_provider] as $id) {
                unset($this->deferred[$id]);
            }
            unset($this->deferred_providers[$service_provider]);
        }

        // handle bindings
        foreach ($service_provider::bind() as $abstract => $concrete) {
            $this->set($abstract, new BindingServiceFactory($concrete));
        }

        $service_provider::register($this->app);

        return null;
    }

    /**
     * @param DeferrableServiceProvider|class-string<ServiceProvider> $service_provider
     */
    public function defer(DeferrableServiceProvider|string $service_provider): null
    {
        if (! \is_a($service_provider, DeferrableServiceProvider::class, true)) {
            throw new InvalidServiceProvider($service_provider);
        }

        // since service providers must be static, we want to just use the class name
        $service_provider = \is_object($service_provider) ? $service_provider::class : $service_provider;
        foreach ($service_provider::provides() as $id) {
            $this->deferred_providers[$service_provider][] = $id;
            $this->deferred[$id] = $service_provider;
        }

        return null;
    }

    private function resolve(string $id): object
    {
        try {
            if (! Type::isClassString($id)) {
                throw new ResolutionFailure(\sprintf('Service "%s" must be a class string', $id));
            }

            // First, check if the service registration for this $id was deferred, and
            // if so, register the provider. Doing so should always put us in a
            // state where we now have either a resolved value or a service factory.
            if (\array_key_exists($id, $this->deferred)) {
                $this->register($this->deferred[$id]);
                if (isset($this->resolved[$id])) {
                    return $this->resolved[$id];
                }

                if (! isset($this->factories[$id])) {
                    throw new ResolutionFailure(\sprintf('Deferred Service "%s" was not registered by its provider', $id));
                }
            }

            // Now we are actually going to try to resolve the value through either
            // defined service factory or through auto-wiring. Both can result in
            // recursive service resolution. We need to track what services we
            // are in the process of resolving, so we can catch and exit circular
            // dependency loops.
            $this->outer_id ??= $id;
            $this->resolving[$id] = isset($this->resolving[$id]) ? throw new CircularDependency($this->outer_id, $id) : true;

            // If there is a service factory defined for the $id, call it.
            if (isset($this->factories[$id])) {
                return ($this->factories[$id])($this->app, $id);
            }

            // Check if the class-string is something we could potentially autowire.
            // If the class we're trying to resolve is an interface, trait, abstract
            // or has a non-public constructor, we can fail here with a "not found".
            // i.e., not an interface, trait, or abstract class,  private constructor.
            $class_reflection = new \ReflectionClass($id);
            if (! $class_reflection->isInstantiable()) {
                throw new NotFound($id);
            }

            // Otherwise, fallback to autowiring the service
            $entry = $class_reflection->newInstanceArgs(\array_map(
                ($this->auto_resolver_callback)(...),
                $class_reflection->getConstructor()?->getParameters() ?? [],
            ));

            $this->logger->debug(\sprintf('Service "%s" Resolved with Fallback Auto-Wiring', $id));

            return $entry;
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'entry_id' => $id,
                'exception' => $e,
                'resolving' => $this->resolving,
                'outer_id' => $this->outer_id,
            ]);
            throw $e instanceof ContainerExceptionInterface ? $e : new ResolutionFailure('Cannot Resolve:' . $id, previous: $e);
        } finally {
            // Unset the tracking variables once we're done resolving an entry
            unset($this->resolving[$id]);
            if ($this->outer_id === $id) {
                $this->outer_id = null;
            }
        }
    }
}
