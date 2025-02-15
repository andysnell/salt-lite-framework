<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigurationFactory;
use PhoneBurner\SaltLite\Framework\App\Event\ApplicationBootstrap;
use PhoneBurner\SaltLite\Framework\App\Event\ApplicationTeardown;
use PhoneBurner\SaltLite\Framework\Container\InvokingContainer;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceContainerFactory;
use Psr\EventDispatcher\EventDispatcherInterface;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

/**
 * This is the main application class. It is a container that holds context,
 * environment state, configuration, and services. It should be the only singleton
 * service in the application, so that tearing it can result in complete garbage
 * collection and reduce the possibility of memory leaks or stale/shared state.
 *
 * While the class is a container, it is not intended to be used as a general-purpose
 * service container itself. The implemented container methods are really shortcuts to
 * the underlying service container.
 */
class App implements MutableContainer, InvokingContainer
{
    private static self|null $instance = null;

    public readonly Environment $environment;

    public readonly ServiceContainer $services;

    public readonly Configuration $config;

    public static function booted(): bool
    {
        return isset(self::$instance);
    }

    public static function instance(): self
    {
        return self::$instance ?? throw new \RuntimeException('Application has not been bootstrapped.');
    }

    public static function bootstrap(Context $context): self
    {
        self::booted() && throw new \RuntimeException('Application has already been bootstrapped.');
        self::$instance = new self($context);
        return self::$instance->setup();
    }

    /**
     * Handle any setup steps that require the application to be fully initialized,
     * e.g. anything that requires the configuration or services to be available,
     * or the path() or env() helper functions.
     */
    private function setup(): self
    {
        $this->services->get(EventDispatcherInterface::class)->dispatch(new ApplicationBootstrap($this));
        return $this;
    }

    public static function teardown(): null
    {
        self::$instance?->cleanup();
        return self::$instance = null;
    }

    /**
     * This method is called when the application is being torn down, providing
     * a hook for any cleanup that needs to be done while we are guaranteed the
     * application is still in a valid state.
     */
    private function cleanup(): void
    {
        $this->services->get(EventDispatcherInterface::class)->dispatch(new ApplicationTeardown($this));
    }

    /**
     * Wrap a callback in the context of an application lifecycle instance. Note
     * that if exit() is called within the callback, the application will still be
     * torn down properly, because App::teardown(...) is registered as a shutdown
     * function.
     *
     * @template T
     * @param callable(App): T $callback
     * @return T
     */
    public static function exec(Context $context, callable $callback): mixed
    {
        $app = self::bootstrap($context);
        try {
            return $callback($app);
        } finally {
            $app::teardown();
        }
    }

    /**
     * Note: in order to avoid chicken-and-egg race conditions, especially as both
     * the configuration and container are dependent on the instance of the App,
     * both factories must return lazy ghost instances, even though the instances
     * will be instantiated almost immediately.
     *
     * Any additional
     */
    private function __construct(public readonly Context $context)
    {
        $this->environment = new Environment($context, APP_ROOT, $_SERVER, $_ENV);
        $this->config = ConfigurationFactory::make($this);
        $this->services = ServiceContainerFactory::make($this);
    }

    public function has(string $id): bool
    {
        return $this->services->has($id);
    }

    public function get(string $id): mixed
    {
        return $this->services->get($id);
    }

    public function set(string $id, mixed $value): void
    {
        $this->services->set($id, $value);
    }

    public function call(
        object|string $object,
        string $method = '__invoke',
        OverrideCollection|null $overrides = null,
    ): mixed {
        return $this->services->call($object, $method, $overrides);
    }
}
