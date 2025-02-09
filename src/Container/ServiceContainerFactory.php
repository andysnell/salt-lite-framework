<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\AppServiceProvider;
use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\CacheServiceProvider;
use PhoneBurner\SaltLite\Framework\Console\ConsoleServiceProvider;
use PhoneBurner\SaltLite\Framework\Container\Exception\InvalidServiceProvider;
use PhoneBurner\SaltLite\Framework\Database\DatabaseServiceProvider;
use PhoneBurner\SaltLite\Framework\EventDispatcher\EventDispatcherServiceProvider;
use PhoneBurner\SaltLite\Framework\HealthCheck\HealthCheckServiceProvider;
use PhoneBurner\SaltLite\Framework\Http\HttpServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\LoggingServiceProvider;
use PhoneBurner\SaltLite\Framework\Mailer\MailerServiceProvider;
use PhoneBurner\SaltLite\Framework\MessageBus\MessageBusServiceProvider;
use PhoneBurner\SaltLite\Framework\Notifier\NotifierServiceProvider;
use PhoneBurner\SaltLite\Framework\Scheduler\SchedulerServiceProvider;
use PhoneBurner\SaltLite\Framework\Storage\StorageServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Helper\Reflect;

class ServiceContainerFactory
{
    /**
     * @var array<class-string<ServiceProvider>>
     */
    public const array FRAMEWORK_PROVIDERS = [
        AppServiceProvider::class,
        MessageBusServiceProvider::class,
        CacheServiceProvider::class,
        ConsoleServiceProvider::class,
        DatabaseServiceProvider::class,
        EventDispatcherServiceProvider::class,
        HealthCheckServiceProvider::class,
        HttpServiceProvider::class,
        LoggingServiceProvider::class,
        MailerServiceProvider::class,
        NotifierServiceProvider::class,
        SchedulerServiceProvider::class,
        StorageServiceProvider::class,
    ];

    public static function make(App $app): ServiceContainer
    {
        return Reflect::ghost(ServiceContainerAdapter::class, static function (ServiceContainerAdapter $ghost) use ($app): void {
            $ghost->__construct($app);

            // Register the service providers in the order they are defined in the
            // framework an application, binding, deferring, and registering services.
            foreach ([...self::FRAMEWORK_PROVIDERS, ...$app->config->get('container.service_providers') ?: []] as $provider) {
                match (true) {
                    \is_a($provider, DeferrableServiceProvider::class, true) => $ghost->deferProvider($provider),
                    \is_a($provider, ServiceProvider::class, true) => $ghost->registerProvider($provider),
                    default => throw new InvalidServiceProvider($provider),
                };
            }

            // Register the App, Configuration, and Environment instances after the
            // service providers have been registered to ensure that they are not
            // accidentally overridden by a service provider definition.
            $ghost->set(Configuration::class, $app->config);
            $ghost->set(Environment::class, $app->environment);
            $ghost->set(App::class, $app);
        });
    }
}
