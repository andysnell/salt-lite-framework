<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\App\App as AppContract;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Configuration\Configuration;
use PhoneBurner\SaltLite\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Container\ServiceContainer;
use PhoneBurner\SaltLite\Container\ServiceContainer\ServiceContainerAdapter;
use PhoneBurner\SaltLite\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\AppServiceProvider;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\CacheServiceProvider;
use PhoneBurner\SaltLite\Framework\Console\ConsoleServiceProvider;
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

use function PhoneBurner\SaltLite\Framework\ghost;

#[Internal]
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
        return ghost(static function (ServiceContainerAdapter $ghost) use ($app): void {
            $ghost->__construct($app);

            // Register the service providers in the order they are defined in the
            // framework an application, binding, deferring, and registering services.
            $deferral_enabled = (bool)$app->config->get('container.enable_deferred_service_registration');
            foreach ([...self::FRAMEWORK_PROVIDERS, ...$app->config->get('container.service_providers') ?: []] as $provider) {
                match (true) {
                    $deferral_enabled && self::deferrable($provider) => $ghost->defer($provider),
                    default => $ghost->register($provider),
                };
            }

            // Register the App, Configuration, and Environment instances after the
            // service providers have been registered to ensure that they are not
            // accidentally overridden by a service provider definition.
            $ghost->set(Configuration::class, $app->config);
            $ghost->set(Environment::class, $app->environment);
            $ghost->set(AppContract::class, $app);
            $ghost->set(App::class, $app);
        });
    }

    /**
     * @phpstan-assert-if-true class-string<DeferrableServiceProvider>|DeferrableServiceProvider $provider
     */
    private static function deferrable(object|string $provider): bool
    {
        return \is_a($provider, DeferrableServiceProvider::class, true);
    }
}
