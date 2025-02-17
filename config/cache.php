<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Util\Serialization\Serializer;
use Symfony\Component\Lock\Store\InMemoryStore;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'cache' => [
        'lock' => [
            'store_driver' => InMemoryStore::class,
        ],
        'drivers' => [
            CacheDriver::Remote->value => [
                'serializer' => env('SALT_REMOTE_CACHE_SERIALIZER', Serializer::Igbinary, Serializer::Php),
            ],
            CacheDriver::File->value => [

            ],
            CacheDriver::Memory->value => [

            ],
        ],
    ],
];
