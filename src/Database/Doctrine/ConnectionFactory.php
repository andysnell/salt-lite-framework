<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Doctrine;

use Doctrine\DBAL\Configuration as ConnectionConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\ConnectionNotFound;
use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Cache\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;

class ConnectionFactory
{
    public const string DEFAULT = 'default';

    public function __construct(
        private readonly Environment $environment,
        private readonly Configuration $configuration,
        private readonly CacheItemPoolFactory $cache_factory,
    ) {
    }

    public function connect(string $name = self::DEFAULT): Connection
    {
        $params = $this->configuration->get("database.doctrine.connections.$name") ?? [];
        if ($params === []) {
            throw new ConnectionNotFound('Unknown Connection: ' . $name);
        }

        $cache_driver = CacheDriver::tryFrom((string)($params['cache_driver'] ?? '')) ?? match ($this->environment->stage) {
            BuildStage::Production => CacheDriver::Remote,
            BuildStage::Integration => match ($this->environment->context) {
                Context::Test => CacheDriver::Memory,
                default => CacheDriver::Remote,
            },
            default => CacheDriver::Memory,
        };

        $connection_config = new ConnectionConfiguration();
        $connection_config->setResultCache(match ($cache_driver) {
            CacheDriver::Remote => $this->cache_factory->make(CacheDriver::Remote, "dbal.$name.result."),
            CacheDriver::Memory => $this->cache_factory->make(CacheDriver::Memory, "dbal.$name.result."),
            CacheDriver::None => $this->cache_factory->make(CacheDriver::None),
            default => throw new \LogicException('Unsupported Cache Type for Doctrine DBAL Result Cache'),
        });

        return DriverManager::getConnection($params, $connection_config);
    }
}
