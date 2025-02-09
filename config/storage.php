<?php

declare(strict_types=1);

use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PhoneBurner\SaltLite\Framework\Storage\StorageDriver;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;

return [
    'storage' => [
        'default' => env('SALT_DEFAULT_STORAGE_ADAPTER', StorageDriver::Local->value),
        'drivers' => [
            StorageDriver::Local->value => [
                'adapter' => LocalFilesystemAdapter::class,
                'root' => path('/storage/app'),
            ],
            StorageDriver::S3->value => [
                'adapter' => AwsS3V3Adapter::class,
                'client' => [
                    'credentials' => [
                        'key' => env('SALT_AWS_S3_ACCESS_KEY_ID'),
                        'secret' => env('SALT_AWS_S3_SECRET_ACCESS_KEY'),
                    ],
                    'region' => env('SALT_AWS_S3_DEFAULT_REGION', 'us-west-1'),
                    'signature' => 'v4',
                    'version' => 'latest',
                ],
                'bucket-name' => env('SALT_AWS_S3_BUCKET_NAME'),
                'prefix' => env('SALT_AWS_S3_PATH_PREFIX'),
            ],
        ],
    ],
];
