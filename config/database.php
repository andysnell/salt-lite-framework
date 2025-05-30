<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Database\Config\AmpqConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\AmpqConnectionConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\DatabaseConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineConnectionConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineEntityManagerConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineMigrationsConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\RedisConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\RedisConnectionConfigStruct;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;

return [
    'database' => new DatabaseConfigStruct(
        ampq: new AmpqConfigStruct(
            connections: [
                'default' => new AmpqConnectionConfigStruct(
                    host: (string)env('SALT_RABBITMQ_HOST', 'rabbitmq'),
                    port: (int)env('SALT_RABBITMQ_PORT', 5672),
                    user: (string)env('SALT_RABBITMQ_USER'),
                    password: (string)env('SALT_RABBITMQ_PASS'),
                ),
            ],
        ),
        redis: new RedisConfigStruct(
            connections: [
                'default' => new RedisConnectionConfigStruct(
                    host: (string)env('SALT_REDIS_HOST', 'redis'),
                    port: (int)env('SALT_REDIS_PORT', 6379),
                ),
            ],
            timeout: 5,
        ),
        doctrine: new DoctrineConfigStruct(
            connections: [
                'default' => new DoctrineConnectionConfigStruct(
                    host: (string)env('SALT_MYSQL_HOST', 'mysql'),
                    port: (int)env('SALT_MYSQL_PORT', 3306),
                    dbname: (string)env('SALT_MYSQL_NAME'),
                    user: (string)env('SALT_MYSQL_USER'),
                    password: (string)env('SALT_MYSQL_PASS'),
                    entity_manager: new DoctrineEntityManagerConfigStruct(
                        entity_paths: [path('/src/')],
                        cache_path: path('/storage/doctrine/default/'),
                        metadata_cache_driver: CacheDriver::instance(env('SALT_DOCTRINE_METADATA_CACHE_DRIVER', CacheDriver::File, CacheDriver::Memory)),
                        query_cache_driver: CacheDriver::instance(env('SALT_DOCTRINE_QUERY_CACHE_DRIVER', CacheDriver::File, CacheDriver::Memory)),
                        result_cache_driver: CacheDriver::instance(env('SALT_DOCTRINE_RESULT_CACHE_DRIVER', CacheDriver::Remote, CacheDriver::Memory)),
                        entity_cache_driver: CacheDriver::instance(env('SALT_DOCTRINE_ENTITY_CACHE_DRIVER', CacheDriver::Remote, CacheDriver::Memory)),
                        event_subscribers: [],
                        mapped_field_types: [],
                    ),
                    migrations: new DoctrineMigrationsConfigStruct(
                        table_storage: [
                            'table_name' => 'doctrine_migration_versions',
                        ],
                        migrations_paths: [
                            'PhoneBurner\SaltLite\Migrations' => path('/database/migrations'),
                        ],
                    ),
                    server_version: env('SALT_MYSQL_SERVER_VERSION'),
                    enable_logging: env('SALT_DOCTRINE_ENABLE_LOGGING', false),
                ),
            ],
            types: [],
        ),
    ),
];
