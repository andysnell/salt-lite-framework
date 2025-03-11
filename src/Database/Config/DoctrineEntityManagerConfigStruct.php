<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Config;

use Doctrine\DBAL\Types\Type;
use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

final readonly class DoctrineEntityManagerConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param list<string> $entity_paths
     * @param list<string> $event_subscribers
     * @param array<class-string|'array'|'bool'|'float'|'int'|'string', class-string<Type>|string> $mapped_field_types
     * @link https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/typedfieldmapper.html
     */
    public function __construct(
        public array $entity_paths = [APP_ROOT . '/src/'],
        public string|null $cache_path = APP_ROOT . '/storage/doctrine/default/',
        public CacheDriver|null $metadata_cache_driver = CacheDriver::Memory,
        public CacheDriver|null $query_cache_driver = CacheDriver::Memory,
        public CacheDriver|null $result_cache_driver = CacheDriver::Memory,
        public CacheDriver|null $entity_cache_driver = CacheDriver::Memory,
        public array $event_subscribers = [],
        public array $mapped_field_types = [],
    ) {
        \assert($entity_paths !== []);
        \assert($cache_path !== '');
    }
}
