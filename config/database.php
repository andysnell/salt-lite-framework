<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;

return [
    'database' => [
        'rabbitmq' => [
            'connections' => [
                'default' => [
                    'host' => env('SALT_RABBITMQ_HOST'),
                    'port' => (int)env('SALT_RABBITMQ_PORT', 5672),
                    'user' => env('SALT_RABBITMQ_USER'),
                    'password' => env('SALT_RABBITMQ_PASS'),
                ],
            ],
        ],
        'redis' => [
            'connections' => [
                'default' => [
                    'host' => env('SALT_REDIS_HOST'),
                    'port' => (int)env('SALT_REDIS_PORT', 6379),
                ],
            ],
            'timeout' => 5,
        ],
        'doctrine' => [
            'connections' => [
                'default' => [
                    'driver' => 'pdo_mysql',
                    'host' => env('SALT_MYSQL_HOST'),
                    'port' => (int)env('SALT_MYSQL_PORT', 3306),
                    'dbname' => env('SALT_MYSQL_NAME'),
                    'user' => env('SALT_MYSQL_USER'),
                    'password' => env('SALT_MYSQL_PASS'),
                    'charset' => 'utf8mb4',
                    'entity_manager' => [
                        'entity_paths' => [
                            path('/src/'),
                        ],
                        'cache_path' => path('/storage/doctrine/default/'),
                        'cache_driver' => [
                            'metadata' => env('SALT_DOCTRINE_METADATA_CACHE_DRIVER', CacheDriver::File->value, CacheDriver::Memory->value),
                            'query' => env('SALT_DOCTRINE_QUERY_CACHE_DRIVER', CacheDriver::File->value, CacheDriver::Memory->value),
                            'result' => env('SALT_DOCTRINE_RESULT_CACHE_DRIVER', CacheDriver::Remote->value, CacheDriver::Memory->value),
                            'entity' => env('SALT_DOCTRINE_ENTITY_CACHE_DRIVER', CacheDriver::Remote->value, CacheDriver::Memory->value),
                        ],
                        'event_subscribers' => [],
                        /** @link https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/reference/typedfieldmapper.html */
                        'mapped_field_types' => [],
                    ],
                    'migrations' => [
                        'table_storage' => [
                            'table_name' => 'doctrine_migration_versions',
                        ],
                        'migrations_paths' => [
                            'PhoneBurner\NumberManagement\Migrations' => path('/database/migrations'),
                        ],
                    ],
                    'enable_logging' => env('SALT_DOCTRINE_ENABLE_LOGGING', false),
                ],
            ],
            /** @link https://www.doctrine-project.org/projects/doctrine-orm/en/3.3/cookbook/custom-mapping-types.html */
            'types' => [
                // Register custom Doctrine types here
            ],
        ],
    ],
];
