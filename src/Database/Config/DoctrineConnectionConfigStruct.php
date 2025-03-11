<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Config;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;

final readonly class DoctrineConnectionConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param CacheDriver|null $result_cache_driver If null, the best driver for the build stage is used
     */
    public function __construct(
        public string $host,
        public int $port,
        public string $dbname,
        public string $user,
        #[\SensitiveParameter] public string $password,
        public DoctrineEntityManagerConfigStruct $entity_manager,
        public DoctrineMigrationsConfigStruct $migrations,
        public CacheDriver|null $result_cache_driver = null,
        public string $driver = 'pdo_mysql',
        public string $charset = 'utf8mb4',
        public array $driver_options = [],
        public bool $enable_logging = false,
    ) {
        \assert($host !== '');
        \assert($port > 0 && $port <= 65535);
        \assert($dbname !== '');
        \assert($user !== '');
        \assert($password !== '');
        \assert($driver !== '');
        \assert($charset !== '');
    }
}
