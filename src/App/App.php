<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigurationFactory;
use PhoneBurner\SaltLite\Framework\Container\InvokingContainer;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainerFactory;

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
final class App implements MutableContainer, InvokingContainer
{
    private static self|null $instance = null;

    public readonly Environment $environment;

    public readonly ServiceContainer $services;

    public readonly Configuration $config;

    public static function bootstrap(Context $context): self
    {
        return self::$instance ??= new self($context);
    }

    public static function booted(): bool
    {
        return isset(self::$instance);
    }

    public static function instance(): self
    {
        return self::$instance ?? throw new \RuntimeException('Application has not been bootstrapped.');
    }

    public static function teardown(): null
    {
        return self::$instance = null;
    }

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
