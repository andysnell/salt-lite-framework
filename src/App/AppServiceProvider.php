<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use Crell\AttributeUtils\Analyzer;
use Crell\AttributeUtils\ClassAnalyzer;
use Crell\AttributeUtils\MemoryCacheAnalyzer;
use Crell\AttributeUtils\Psr6CacheAnalyzer;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\App\BuildStage;
use PhoneBurner\SaltLite\App\Context;
use PhoneBurner\SaltLite\App\Exception\KernelError;
use PhoneBurner\SaltLite\App\Kernel;
use PhoneBurner\SaltLite\Attribute\AttributeAnalyzer;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Cache\CacheDriver;
use PhoneBurner\SaltLite\Cache\Psr6\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Clock\Clock;
use PhoneBurner\SaltLite\Clock\HighResolutionTimer;
use PhoneBurner\SaltLite\Clock\SystemClock;
use PhoneBurner\SaltLite\Clock\SystemHighResolutionTimer;
use PhoneBurner\SaltLite\Configuration\Configuration;
use PhoneBurner\SaltLite\Container\Exception\NotResolvable;
use PhoneBurner\SaltLite\Container\InvokingContainer;
use PhoneBurner\SaltLite\Container\MutableContainer;
use PhoneBurner\SaltLite\Container\ServiceContainer;
use PhoneBurner\SaltLite\Container\ServiceContainer\ServiceContainerAdapter;
use PhoneBurner\SaltLite\Container\ServiceProvider;
use PhoneBurner\SaltLite\Cryptography\Defaults;
use PhoneBurner\SaltLite\Cryptography\KeyManagement\KeyChain;
use PhoneBurner\SaltLite\Cryptography\Natrium;
use PhoneBurner\SaltLite\Framework\App\App as FrameworkApp;
use PhoneBurner\SaltLite\Framework\App\Config\AppConfigStruct;
use PhoneBurner\SaltLite\Framework\Console\CliKernel;
use PhoneBurner\SaltLite\Framework\Http\HttpKernel;
use PhoneBurner\SaltLite\Logging\LogTrace;
use PhoneBurner\SaltLite\Type\Type;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class AppServiceProvider implements ServiceProvider
{
    public static function bind(): array
    {
        return [
            ClockInterface::class => Clock::class,
            ClassAnalyzer::class => AttributeAnalyzer::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        // These services must be set explicitly in the container by the application
        // after service provider registration.
        $app->set(App::class, static fn (App $app): never => throw new NotResolvable(App::class));
        $app->set(FrameworkApp::class, static fn (App $app): never => throw new NotResolvable(FrameworkApp::class));
        $app->set(Environment::class, static fn (App $app): never => throw new NotResolvable(Environment::class));
        $app->set(Configuration::class, static fn (App $app): never => throw new NotResolvable(Configuration::class));

        // When asked for a concrete instance or an implementation of either of
        // the two container interfaces, the container should return itself, unless
        // specifically asking for the ServiceContainer. These are defined here,
        // and not in the bind method, since they already exist and lazy loading
        // would add unnecessary overhead.
        $app->set(ContainerInterface::class, $app);
        $app->set(InvokingContainer::class, $app);
        $app->set(MutableContainer::class, $app);
        $app->set(ServiceContainer::class, $app->services);
        $app->set(ServiceContainerAdapter::class, $app->services);

        // These are the few services that should always be eagerly instantiated
        // since they are used on every request and are less expensive to create
        // than to wrap with a closure to defer instantiation.
        $app->set(LogTrace::class, LogTrace::make());
        $app->set(BuildStage::class, $app->environment->stage);
        $app->set(Context::class, $app->environment->context);
        $app->set(Clock::class, new SystemClock());
        $app->set(HighResolutionTimer::class, new SystemHighResolutionTimer());

        // Note: we use a regular closure here instead of binding the interface to
        // a concrete implementation because we may be in a context where there
        // is no kernel available (e.g. running tests), and this gives us a clean
        // way to fail in that case.
        $app->set(Kernel::class, static fn(App $app): Kernel => $app->services->get(match ($app->context) {
            Context::Http => HttpKernel::class,
            Context::Cli => CliKernel::class,
            default => throw new KernelError('Salt Context is Not Defined or Supported'),
        }));

        $app->set(Natrium::class, static function (App $app): Natrium {
            return new Natrium(
                new KeyChain(Type::of(AppConfigStruct::class, $app->config->get('app'))->key),
                $app->get(Clock::class),
                new Defaults(
                    $app->config->get('app')->symmetric_algorithm,
                    $app->config->get('app')->asymmetric_algorithm,
                ),
            );
        });

        $app->set(
            AttributeAnalyzer::class,
            static fn(App $app): AttributeAnalyzer => new AttributeAnalyzer(ghost(static function (MemoryCacheAnalyzer $ghost) use ($app): void {
                $ghost->__construct(
                    new Psr6CacheAnalyzer(
                        new Analyzer(),
                        $app->services->get(CacheItemPoolFactory::class)->make(match ($app->environment->stage) {
                            BuildStage::Development => CacheDriver::Memory,
                            default => CacheDriver::File,
                        }),
                    ),
                );
            })),
        );
    }
}
