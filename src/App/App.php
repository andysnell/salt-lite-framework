<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\App\App as AppContract;
use PhoneBurner\SaltLite\App\Context;
use PhoneBurner\SaltLite\App\Event\ApplicationBootstrap;
use PhoneBurner\SaltLite\App\Event\ApplicationTeardown;
use PhoneBurner\SaltLite\Configuration\Configuration;
use PhoneBurner\SaltLite\Configuration\ConfigurationFactory;
use PhoneBurner\SaltLite\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Container\ServiceContainer;
use PhoneBurner\SaltLite\Framework\App\ErrorHandling\ErrorHandler;
use PhoneBurner\SaltLite\Framework\App\ErrorHandling\ExceptionHandler;
use PhoneBurner\SaltLite\Framework\App\ErrorHandling\NullErrorHandler;
use PhoneBurner\SaltLite\Framework\App\ErrorHandling\NullExceptionHandler;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainerFactory;
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
class App implements AppContract
{
    private static self|null $instance = null;

    public Environment $environment;

    public ServiceContainer $services;

    public Configuration $config;

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
        // set error handler
        $error_handler = $this->services->get(ErrorHandler::class);
        if (! $error_handler instanceof NullErrorHandler) {
            \set_error_handler($error_handler);
        }

        // set exception handler
        $exception_handler = $this->services->get(ExceptionHandler::class);
        if (! $exception_handler instanceof NullExceptionHandler) {
            \set_exception_handler($exception_handler);
        }

        // dispatch bootstrap event
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
     * For example, configuration files may use functions like path() or env()
     * which may be dependent on the App instance.
     */
    private function __construct(public Context $context)
    {
        $this->environment = new Environment($context, APP_ROOT, $_SERVER, $_ENV);
        $this->config = ConfigurationFactory::make($this->environment);
        $this->services = ServiceContainerFactory::make($this);
    }

    public function has(\Stringable|string $id): bool
    {
        return $this->services->has($id);
    }

    public function get(\Stringable|string $id): mixed
    {
        return $this->services->get($id);
    }

    public function set(\Stringable|string $id, mixed $value): void
    {
        $this->services->set($id, $value);
    }

    public function unset(\Stringable|string $id): void
    {
        $this->services->unset($id);
    }

    public function call(
        object|string $object,
        string $method = '__invoke',
        OverrideCollection|null $overrides = null,
    ): mixed {
        return $this->services->call($object, $method, $overrides);
    }
}
