<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConnectionProvider as DoctrineConnectionProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider as DoctrineEntityManagerProvider;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Cache\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionFactory;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionProvider;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Orm\EntityManagerFactory;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Orm\EntityManagerProvider;
use PhoneBurner\SaltLite\Framework\Database\Redis\CachingRedisManager;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Reflect;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class DatabaseServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            RedisManager::class,
            DoctrineConnectionProvider::class,
            DoctrineEntityManagerProvider::class,
            CachingRedisManager::class,
            ConnectionProvider::class,
            ConnectionFactory::class,
            Connection::class,
            EntityManagerProvider::class,
            EntityManagerFactory::class,
            EntityManagerInterface::class,
        ];
    }

    public static function bind(): array
    {
        return [
            RedisManager::class => CachingRedisManager::class,
            DoctrineConnectionProvider::class => ConnectionProvider::class,
            DoctrineEntityManagerProvider::class => EntityManagerProvider::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            CachingRedisManager::class,
            static fn(App $app): CachingRedisManager => Reflect::ghost(
                CachingRedisManager::class,
                static function (CachingRedisManager $ghost) use ($app): void {
                    $ghost->__construct($app->config);
                },
            ),
        );

        $app->set(
            \Redis::class,
            static fn(App $app): \Redis => $app->get(RedisManager::class)->connect(),
        );

        $app->set(
            ConnectionProvider::class,
            static fn(App $app): ConnectionProvider => new ConnectionProvider(
                $app->services,
            ),
        );

        $app->set(
            ConnectionFactory::class,
            static fn(App $app): ConnectionFactory => new ConnectionFactory(
                $app->environment,
                $app->config,
                $app->get(CacheItemPoolFactory::class),
                $app->get(LoggerInterface::class),
            ),
        );

        $app->set(
            Connection::class,
            static fn(App $app): Connection => $app->get(ConnectionFactory::class)->connect(),
        );

        $app->set(
            EntityManagerProvider::class,
            static fn(App $app): EntityManagerProvider => new EntityManagerProvider(
                $app->services,
            ),
        );

        $app->set(
            EntityManagerFactory::class,
            static fn(App $app): EntityManagerFactory => new EntityManagerFactory(
                $app->services,
                $app->environment,
                $app->config,
                $app->get(DoctrineConnectionProvider::class),
                $app->get(CacheItemPoolFactory::class),
            ),
        );

        $app->set(
            EntityManagerInterface::class,
            static fn(App $app): EntityManagerInterface => $app->get(EntityManagerFactory::class)->init(),
        );
    }
}
