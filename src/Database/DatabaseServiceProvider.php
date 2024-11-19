<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Tools\Console\ConnectionProvider as DoctrineConnectionProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider as DoctrineEntityManagerProvider;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionFactory;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionProvider;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Orm\EntityManagerFactory;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Orm\EntityManagerProvider;
use PhoneBurner\SaltLite\Framework\Database\Redis\CachingRedisManager;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class DatabaseServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->bind(RedisManager::class, CachingRedisManager::class);
        $container->set(
            CachingRedisManager::class,
            static function (ContainerInterface $container): CachingRedisManager {
                return new CachingRedisManager($container->get(Configuration::class));
            },
        );

        $container->set(
            \Redis::class,
            static function (ContainerInterface $container): \Redis {
                return $container->get(RedisManager::class)->connect();
            },
        );

        $container->bind(DoctrineConnectionProvider::class, ConnectionProvider::class);
        $container->set(
            ConnectionProvider::class,
            static function (ContainerInterface $container): ConnectionProvider {
                return new ConnectionProvider($container);
            },
        );

        $container->set(
            ConnectionFactory::class,
            static function (ContainerInterface $container): ConnectionFactory {
                $environment = $container->get(Environment::class);
                return new ConnectionFactory(
                    $environment,
                    $container->get(Configuration::class),
                    $container->get(CacheItemPoolFactory::class),
                );
            },
        );

        $container->set(
            Connection::class,
            static function (ContainerInterface $container): Connection {
                return $container->get(ConnectionFactory::class)->connect();
            },
        );

        $container->bind(DoctrineEntityManagerProvider::class, EntityManagerProvider::class);

        $container->set(
            EntityManagerProvider::class,
            static function (ContainerInterface $container): EntityManagerProvider {
                return new EntityManagerProvider($container);
            },
        );

        $container->set(
            EntityManagerFactory::class,
            static function (ContainerInterface $container): EntityManagerFactory {
                return new EntityManagerFactory(
                    $container,
                    $container->get(Environment::class),
                    $container->get(Configuration::class),
                    $container->get(DoctrineConnectionProvider::class),
                    $container->get(CacheItemPoolFactory::class),
                );
            },
        );

        $container->set(
            EntityManagerInterface::class,
            static function (ContainerInterface $container): EntityManagerInterface {
                return $container->get(EntityManagerFactory::class)->init();
            },
        );
    }
}
