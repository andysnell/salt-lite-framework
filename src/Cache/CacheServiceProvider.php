<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\App\Exception\InvalidConfiguration;
use PhoneBurner\SaltLite\Framework\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKeyFactory;
use PhoneBurner\SaltLite\Framework\Cache\Lock\SymfonyLockFactoryAdapter;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Lock\LockFactory as SymfonyLockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Lock\Store\RedisStore;

use function PhoneBurner\SaltLite\Framework\ghost;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class CacheServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            AppendOnlyCache::class,
            Cache::class,
            InMemoryCache::class,
            CacheInterface::class,
            CacheItemPoolInterface::class,
            CacheItemPoolFactory::class,
            NamedKeyFactory::class,
            LockFactory::class,
        ];
    }

    public static function bind(): array
    {
        return [];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            AppendOnlyCache::class,
            ghost(static fn(AppendOnlyCacheAdapter $ghost): null => $ghost->__construct(
                $app->get(CacheItemPoolFactory::class)->make(CacheDriver::File),
            )),
        );

        $app->set(
            Cache::class,
            ghost(static fn(CacheAdapter $ghost): null => $ghost->__construct(
                $app->get(CacheInterface::class),
            )),
        );

        $app->set(
            InMemoryCache::class,
            ghost(static fn(InMemoryCache $ghost): null => $ghost->__construct(
                $app->get(CacheItemPoolFactory::class)->make(CacheDriver::Memory),
            )),
        );

        $app->set(
            CacheInterface::class,
            ghost(static fn(Psr16Cache $ghost): null => $ghost->__construct(
                $app->get(CacheItemPoolFactory::class)->make(CacheDriver::Remote),
            )),
        );

        $app->set(
            CacheItemPoolInterface::class,
            static fn(App $app): CacheItemPoolInterface => $app->get(CacheItemPoolFactory::class)->make(CacheDriver::Remote),
        );

        $app->set(
            CacheItemPoolFactory::class,
            ghost(static fn(CacheItemPoolFactory $ghost): null => $ghost->__construct(
                $app->environment,
                $app->get(RedisManager::class),
                $app->get(LoggerInterface::class),
            )),
        );

        $app->set(NamedKeyFactory::class, new NewInstanceServiceFactory());

        $app->set(
            LockFactory::class,
            ghost(static function (SymfonyLockFactoryAdapter $ghost) use ($app): void {
                $store_driver = $app->config->get('cache.lock.store_driver');
                $store_driver = match (true) {
                    $app->environment->context === Context::Test, $store_driver === InMemoryStore::class => InMemoryStore::class,
                    $app->environment->stage === BuildStage::Production, $store_driver === RedisStore::class => RedisStore::class,
                    default => throw new InvalidConfiguration('Invalid Cache Lock Store Driver'),
                };

                $ghost->__construct(
                    $app->get(NamedKeyFactory::class),
                    new SymfonyLockFactory(match ($store_driver) {
                        InMemoryStore::class => new InMemoryStore(),
                        RedisStore::class => ghost(static fn(RedisStore $ghost): null => $ghost->__construct(
                            $app->get(RedisManager::class)->connect(),
                        )),
                    }),
                );

                if ($app->environment->stage !== BuildStage::Production) {
                    $ghost->setLogger($app->get(LoggerInterface::class));
                }
            }),
        );
    }
}
