<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use Crell\AttributeUtils\Analyzer;
use Crell\AttributeUtils\ClassAnalyzer;
use Crell\AttributeUtils\MemoryCacheAnalyzer;
use Crell\AttributeUtils\Psr6CacheAnalyzer;
use PhoneBurner\SaltLite\Framework\App\Clock\Clock;
use PhoneBurner\SaltLite\Framework\App\Clock\HighResolutionTimer;
use PhoneBurner\SaltLite\Framework\App\Clock\SystemClock;
use PhoneBurner\SaltLite\Framework\App\Clock\SystemHighResolutionTimer;
use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Exception\KernelError;
use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Cache\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Framework\Console\CliKernel;
use PhoneBurner\SaltLite\Framework\Container\Exception\NotResolvable;
use PhoneBurner\SaltLite\Framework\Container\InvokingContainer;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainerAdapter;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Http\HttpKernel;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Analyzer\AttributeAnalyzer;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Crypto\AppKey;
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
        $app->set(Environment::class, static fn (App $app): never => throw new NotResolvable(Environment::class));
        $app->set(Configuration::class, static fn (App $app): never => throw new NotResolvable(Configuration::class));

        // When asked for a concrete instance or an implementation of either of
        // the two container interfaces, the container should return itself.
        $app->set(ContainerInterface::class, $app);
        $app->set(InvokingContainer::class, $app);
        $app->set(MutableContainer::class, $app);
        $app->set(ServiceContainer::class, $app->services);
        $app->set(ServiceContainerAdapter::class, $app->services);

        $app->set(LogTrace::class, LogTrace::make(...));
        $app->set(BuildStage::class, static fn(App $app): BuildStage => $app->environment->stage);
        $app->set(Context::class, static fn(App $app): Context => $app->environment->context);

        $app->set(Kernel::class, static fn(App $app): Kernel => $app->services->get(match ($app->context) {
            Context::Http => HttpKernel::class,
            Context::Cli => CliKernel::class,
            default => throw new KernelError('Salt Context is Not Defined or Supported'),
        }));

        $app->set(AppKey::class, static fn(App $app): AppKey => new AppKey($app->config->get('app.key') ?: throw new \LogicException(
            'A Valid App Key Must Be Defined in Configuration',
        )));

        $app->set(Clock::class, static fn(App $app): SystemClock => new SystemClock());
        $app->set(HighResolutionTimer::class, static fn(App $app): SystemHighResolutionTimer => new SystemHighResolutionTimer());

        $app->set(AttributeAnalyzer::class, ghost(static function (MemoryCacheAnalyzer $ghost) use ($app): void {
            $ghost->__construct(
                new Psr6CacheAnalyzer(
                    new Analyzer(),
                    $app->services->get(CacheItemPoolFactory::class)->make(match ($app->environment->stage) {
                        BuildStage::Development => CacheDriver::Memory,
                        default => CacheDriver::File,
                    }),
                ),
            );
        }));
    }
}
