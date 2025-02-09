<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Doctrine\Orm;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Tools\Console\ConnectionProvider as DoctrineConnectionProvider;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\Region\DefaultRegion;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration as EntityManagerConfiguration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\DefaultTypedFieldMapper;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\UnknownManagerException;
use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Cache\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Cache\CacheRegion;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Cache\CacheType;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionFactory;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Types;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeConstant;
use Psr\Container\ContainerInterface;

class EntityManagerFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly Environment $environment,
        private readonly Configuration $configuration,
        private readonly DoctrineConnectionProvider $connection_provider,
        private readonly CacheItemPoolFactory $cache_factory,
    ) {
    }

    /**
     * To support namespaced annotations, we're not using the simple annotation driver
     *
     * Why are we globally ignoring parsing the "@mixin" annotations?
     *
     * Doctrine will recursively parse the annotations of vendor classes
     * that are used in creating the properties of entities while querying
     * the database. This can have unexpected consequences when Doctrine
     * and a third party vendor disagree on what is the "standard" for a
     * particular annotation, in this case the way that the Carbon library
     * uses the "@mixin" annotation is not compatible with the way that
     * the Doctrine Annotation library handles resolving class names. This
     * bug has been reported on the issue tracker for both libraries, but
     * both closed the issues as a "wont-fix" and claim the other library
     * is responsible to fix.
     *
     * Note: The even though the metadata, query, and entity caches may use the
     * append-only PHP file cache driver, we use separate instances in order to
     * clear each cache independently, and independent of the main append-only
     * cache pool.
     *
     * @link https://github.com/briannesbitt/Carbon/issues/2525
     * @link https://github.com/doctrine/annotations/pull/293
     */
    public function init(
        string $name = ConnectionFactory::DEFAULT,
    ): EntityManagerInterface {
        $this->registerCustomTypes();

        $config = $this->configuration->get("database.doctrine.connections.$name.entity_manager") ?? [];
        if ($config === []) {
            $known_managers = \array_keys($this->configuration->get("database.doctrine.entity_managers") ?? []);
            throw UnknownManagerException::unknownManager($name, \array_map(\strval(...), $known_managers));
        }

        $cache_path = $config['cache_path'] ?? (\sys_get_temp_dir() . '/doctrine/' . $name . '/');

        $doctrine_config = new EntityManagerConfiguration();
        $doctrine_config->setEntityListenerResolver(new EntityListenerContainerResolver($this->container));

        $doctrine_config->setProxyDir($cache_path . '/proxy');
        $doctrine_config->setProxyNamespace(\ucfirst($name) . 'DoctrineProxies');
        $doctrine_config->setAutoGenerateProxyClasses(match ($this->environment->stage) {
            BuildStage::Production => ProxyFactory::AUTOGENERATE_NEVER,
            default => ProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED,
        });

        $doctrine_config->setMetadataDriverImpl(new AttributeDriver($config['entity_paths'] ?? [], true));
        $doctrine_config->setMetadataCache(match ($this->getCacheDriver(CacheType::Metadata, $config)) {
            CacheDriver::File => $this->cache_factory->createFileCacheItemPool(CacheType::Metadata->value, $cache_path),
            CacheDriver::Memory => $this->cache_factory->make(CacheDriver::Memory, "orm.$name.metadata."),
            CacheDriver::None => $this->cache_factory->make(CacheDriver::None),
            default => throw new \LogicException('Unsupported Cache Type for Doctrine ORM Metadata Cache'),
        });

        $doctrine_config->setQueryCache(match ($this->getCacheDriver(CacheType::Query, $config)) {
            CacheDriver::File => $this->cache_factory->createFileCacheItemPool(CacheType::Query->value, $cache_path),
            CacheDriver::Memory => $this->cache_factory->make(CacheDriver::Memory, "orm.$name.query."),
            CacheDriver::None => $this->cache_factory->make(CacheDriver::None),
            default => throw new \LogicException('Unsupported Cache Type for Doctrine ORM Query Cache'),
        });

        $doctrine_config->setResultCache(match ($this->getCacheDriver(CacheType::Result, $config)) {
            CacheDriver::Remote => $this->cache_factory->make(CacheDriver::Remote, "orm.$name.result."),
            CacheDriver::Memory => $this->cache_factory->make(CacheDriver::Memory, "orm.$name.result."),
            CacheDriver::None => $this->cache_factory->make(CacheDriver::None),
            default => throw new \LogicException('Unsupported Cache Type for Doctrine ORM Result Cache'),
        });

        $this->configureEntityCache($doctrine_config, $config, $name, $cache_path);

        $mapped_field_types = $config['mapped_field_types'] ?? [];
        if ($mapped_field_types) {
            $doctrine_config->setTypedFieldMapper(new DefaultTypedFieldMapper($mapped_field_types));
        }

        $em = new EntityManager($this->connection_provider->getConnection($name), $doctrine_config);

        foreach ($config['event_subscribers'] ?? [] as $subscriber) {
            $subscriber = $this->container->get($subscriber);
            \assert($subscriber instanceof EventSubscriber);
            $em->getEventManager()->addEventSubscriber($subscriber);
        }

        return $em;
    }

    private function registerCustomTypes(): void
    {
        static $registered = false;
        if ($registered) {
            return;
        }

        foreach ([Types::REGISTRATION_MAP, $this->configuration->get("database.doctrine.types") ?? []] as $types) {
            foreach ($types as $type_name => $class_name) {
                Type::addType($type_name, $class_name);
            }
        }

        $registered = true;
    }

    private function configureEntityCache(
        EntityManagerConfiguration $doctrine_config,
        array $config,
        string $name,
        string $cache_path,
    ): void {
        $cache_driver = $this->getCacheDriver(CacheType::Entity, $config);
        if ($cache_driver === CacheDriver::None) {
            return;
        }

        $regions_config = new RegionsConfiguration(TimeConstant::SECONDS_IN_HOUR);

        $factory = new DefaultCacheFactory($regions_config, match ($cache_driver) {
            CacheDriver::Remote => $this->cache_factory->make(CacheDriver::Remote, "orm.$name.entity."),
            CacheDriver::Memory => $this->cache_factory->make(CacheDriver::Memory, "orm.$name.entity."),
            default => throw new \LogicException('Unsupported Cache Type for Doctrine ORM Entity Cache'),
        });

        $factory->setRegion(new DefaultRegion(CacheRegion::APPEND_ONLY, match ($cache_driver) {
            CacheDriver::File, CacheDriver::Remote => $this->cache_factory->createFileCacheItemPool(CacheType::Entity->value, $cache_path),
            CacheDriver::Memory => $this->cache_factory->make(CacheDriver::Memory, "orm.$name.entity."),
            default => throw new \LogicException('Unsupported Cache Type for Doctrine ORM Entity Cache (Append Only Region)'),
        }));

        $doctrine_config->setSecondLevelCacheEnabled(true);
        $doctrine_config->getSecondLevelCacheConfiguration()?->setRegionsConfiguration($regions_config);
        $doctrine_config->getSecondLevelCacheConfiguration()?->setCacheFactory($factory);
    }

    private function getCacheDriver(CacheType $type, array $config): CacheDriver
    {
        return CacheDriver::tryFrom((string)($config['cache_driver'][$type->value] ?? '')) ?? match ($type) {
            CacheType::Metadata, CacheType::Query => match ($this->environment->stage) {
                BuildStage::Production, BuildStage::Integration => CacheDriver::File,
                default => CacheDriver::Memory,
            },
            CacheType::Result, CacheType::Entity => match ($this->environment->stage) {
                BuildStage::Production => CacheDriver::Remote,
                BuildStage::Integration => match ($this->environment->context) {
                    Context::Test => CacheDriver::Memory,
                    default => CacheDriver::Remote,
                },
                default => CacheDriver::Memory,
            },
        };
    }
}
