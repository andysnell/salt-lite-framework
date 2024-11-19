<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKeyFactory;
use PhoneBurner\SaltLite\Framework\Cache\Lock\SymfonyLockFactoryAdapter;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Lock\LockFactory as SymfonyLockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class CacheServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->set(
            AppendOnlyCache::class,
            static function (ContainerInterface $container): AppendOnlyCacheAdapter {
                return new AppendOnlyCacheAdapter(
                    $container->get(CacheItemPoolFactory::class)->make(CacheDriver::File),
                );
            },
        );

        $container->set(
            Cache::class,
            static function (ContainerInterface $container): CacheAdapter {
                return new CacheAdapter(
                    $container->get(CacheItemPoolFactory::class)->make(CacheDriver::Remote),
                );
            },
        );

        $container->set(
            InMemoryCache::class,
            static function (ContainerInterface $container): InMemoryCache {
                return new InMemoryCache(
                    $container->get(CacheItemPoolFactory::class)->make(CacheDriver::Memory),
                );
            },
        );

        $container->set(
            CacheInterface::class,
            static function (ContainerInterface $container): CacheInterface {
                return new Psr16Cache(
                    $container->get(CacheItemPoolFactory::class)->make(CacheDriver::Remote),
                );
            },
        );

        $container->set(
            CacheItemPoolInterface::class,
            static function (ContainerInterface $container): CacheItemPoolInterface {
                return $container->get(CacheItemPoolFactory::class)->make(CacheDriver::Remote);
            },
        );

        $container->set(
            CacheItemPoolFactory::class,
            static function (ContainerInterface $container): CacheItemPoolFactory {
                $environment = $container->get(Environment::class);
                return new CacheItemPoolFactory(
                    $environment,
                    $container->get(RedisManager::class),
                    $container->get(LoggerInterface::class),
                );
            },
        );

        $container->set(
            NamedKeyFactory::class,
            static function (ContainerInterface $container): NamedKeyFactory {
                return new NamedKeyFactory();
            },
        );

        $container->set(
            LockFactory::class,
            static function (ContainerInterface $container): SymfonyLockFactoryAdapter {
                $environment = $container->get(Environment::class);
                $lock_factory = new SymfonyLockFactoryAdapter(
                    $container->get(NamedKeyFactory::class),
                    new SymfonyLockFactory(match ($environment->context) {
                    Context::Test => new InMemoryStore(),
                    default => new RedisStore($container->get(\Redis::class)),
                    }),
                );

                if ($environment->stage !== BuildStage::Production) {
                    $lock_factory->setLogger($container->get(LoggerInterface::class));
                }

                return $lock_factory;
            },
        );
    }
}
