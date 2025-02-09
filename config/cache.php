<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Cache\Marshaller\Serializer;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'cache' => [
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
