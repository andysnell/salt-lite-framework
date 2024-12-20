<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Configuration\ConfigurationFactory;
use PhoneBurner\SaltLite\Framework\Configuration\ImmutableConfiguration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\PhpDiContainerAdapter;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Helper\Reflect;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

final class App
{
    private static self|null $instance = null;

    public readonly MutableContainer $container;

    public readonly Configuration $config;

    public readonly Environment $environment;

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

    public static function teardown(): void
    {
        self::$instance = null;
    }

    private function __construct(
        public readonly Context $context,
        Environment|null $environment = null,
        Configuration|null $config = null,
        MutableContainer|null $container = null,
    ) {
        $this->environment = $environment ?? new Environment($context, APP_ROOT);

        $this->config = $config ?? Reflect::proxy(
            ImmutableConfiguration::class,
            fn(ImmutableConfiguration $object): ImmutableConfiguration => ConfigurationFactory::make($this->environment),
        );

        $this->container = $container ?? Reflect::ghost(PhpDiContainerAdapter::class, function (PhpDiContainerAdapter $container): void {
            $container->__construct();
            foreach ($this->config->get('container.service_providers') as $provider_class) {
                \assert(\is_a($provider_class, ServiceProvider::class, true));
                new $provider_class()->register($container);
            }

            $container->set(Configuration::class, $this->config);
            $container->set(Environment::class, $this->environment);
            $container->set(LogTrace::class, LogTrace::make());
            $container->set(self::class, $this);
        });
    }
}
