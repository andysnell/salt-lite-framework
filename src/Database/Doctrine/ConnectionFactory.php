<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Doctrine;

use Doctrine\DBAL\Configuration as ConnectionConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\DBAL\Tools\Console\ConnectionNotFound;
use PhoneBurner\SaltLite\App\BuildStage;
use PhoneBurner\SaltLite\App\Context;
use PhoneBurner\SaltLite\App\Environment;
use PhoneBurner\SaltLite\Cache\CacheDriver;
use PhoneBurner\SaltLite\Cache\Psr6\CacheItemPoolFactory;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineConnectionConfigStruct;
use Psr\Log\LoggerInterface;

class ConnectionFactory
{
    public const string DEFAULT = 'default';

    public function __construct(
        private readonly Environment $environment,
        private readonly DoctrineConfigStruct $config,
        private readonly CacheItemPoolFactory $cache_factory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function connect(string $name = self::DEFAULT): Connection
    {
        $config = $this->config->connections[$name] ?? throw new ConnectionNotFound(
            'Connection Not Defined In Configuration: ' . $name,
        );

        \assert($config instanceof DoctrineConnectionConfigStruct);

        $connection_config = new ConnectionConfiguration();
        $connection_config->setResultCache(match ($this->resolveCacheDriver($config->result_cache_driver)) {
            CacheDriver::Remote => $this->cache_factory->make(CacheDriver::Remote, \sprintf('dbal.%s.result.', $name)),
            CacheDriver::Memory => $this->cache_factory->make(CacheDriver::Memory, \sprintf('dbal.%s.result.', $name)),
            CacheDriver::None => $this->cache_factory->make(CacheDriver::None),
            default => throw new \LogicException('Unsupported Cache Type for Doctrine DBAL Result Cache'),
        });

        if ($config->enable_logging) {
            $middleware = $connection_config->getMiddlewares();
            $middleware[] = new Middleware($this->logger);
            $connection_config->setMiddlewares($middleware);
        }

        \assert(\in_array($config->driver, DriverManager::getAvailableDrivers(), true));

        return DriverManager::getConnection([
            'host' => $config->host,
            'port' => $config->port,
            'dbname' => $config->dbname,
            'user' => $config->user,
            'password' => $config->password,
            'driver' => $config->driver,
            'charset' => $config->charset,
            'driverOptions' => $config->driver_options,
        ], $connection_config);
    }

    private function resolveCacheDriver(CacheDriver|null $cache_driver): CacheDriver
    {
        if ($this->environment->context === Context::Test) {
            return CacheDriver::Memory;
        }

        return $cache_driver ?? match ($this->environment->stage) {
            BuildStage::Production, BuildStage::Integration => CacheDriver::Remote,
            default => CacheDriver::Memory,
        };
    }
}
