<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use Crell\AttributeUtils\Analyzer;
use Crell\AttributeUtils\ClassAnalyzer;
use Crell\AttributeUtils\MemoryCacheAnalyzer;
use Crell\AttributeUtils\Psr6CacheAnalyzer;
use PhoneBurner\SaltLite\Framework\App\Exception\KernelError;
use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Cache\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Console\CliKernel;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\PhpDiContainerAdapter;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Http\HttpKernel;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Clock\Clock;
use PhoneBurner\SaltLite\Framework\Util\Clock\HighResolutionTimer;
use PhoneBurner\SaltLite\Framework\Util\Clock\SystemClock;
use PhoneBurner\SaltLite\Framework\Util\Clock\SystemHighResolutionTimer;
use PhoneBurner\SaltLite\Framework\Util\Crypto\AppKey;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;

#[Internal('Override Definitions in Application Service Providers')]
class AppServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        // When asked for a concrete instance or an implementation of either of
        // the two container interfaces, the container should return itself.
        $container->set(ContainerInterface::class, $container);
        $container->set(MutableContainer::class, $container);
        $container->set(PhpDiContainerAdapter::class, $container);

        $container->set(
            Environment::class,
            static function (ContainerInterface $container): never {
                throw new \LogicException('Environment is not defined');
            },
        );

        $container->set(
            BuildStage::class,
            static function (ContainerInterface $container): BuildStage {
                return $container->get(Environment::class)->stage;
            },
        );

        $container->set(
            Context::class,
            static function (ContainerInterface $container): Context {
                return $container->get(Environment::class)->context;
            },
        );

        $container->set(
            Kernel::class,
            static function (ContainerInterface $container): Kernel {
                return match ($container->get(Context::class)) {
                    Context::Http => $container->get(HttpKernel::class),
                    Context::Cli => $container->get(CliKernel::class),
                    default => throw new KernelError('Salt Context is Not Defined or Supported'),
                };
            },
        );

        $container->set(
            AppKey::class,
            static function (ContainerInterface $container): AppKey {
                return new AppKey(
                    $container->get(Configuration::class)->get('app.key') ?: throw new \LogicException('App Key not defined'),
                );
            },
        );

        $container->bind(ClockInterface::class, Clock::class);
        $container->bind(Clock::class, SystemClock::class);
        $container->set(
            SystemClock::class,
            static function (ContainerInterface $container): SystemClock {
                return new SystemClock();
            },
        );

        $container->set(
            HighResolutionTimer::class,
            static function (ContainerInterface $container): SystemHighResolutionTimer {
                return new SystemHighResolutionTimer();
            },
        );

        $container->set(
            ClassAnalyzer::class,
            static function (ContainerInterface $container): ClassAnalyzer {
                $analyzer = new Analyzer();
                if ($container->get(BuildStage::class) !== BuildStage::Development) {
                    $pool = $container->get(CacheItemPoolFactory::class)->make(CacheDriver::File);
                    $analyzer = new Psr6CacheAnalyzer($analyzer, $pool);
                }

                return new MemoryCacheAnalyzer($analyzer);
            },
        );
    }
}
