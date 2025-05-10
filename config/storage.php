<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Storage\Config\LocalFilesystemConfigStruct;
use PhoneBurner\SaltLite\Framework\Storage\Config\S3FilesystemConfigStruct;
use PhoneBurner\SaltLite\Framework\Storage\Config\StorageConfigStruct;
use PhoneBurner\SaltLite\Framework\Storage\StorageDriver;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;

return [
    'storage' => new StorageConfigStruct(
        default: env('SALT_DEFAULT_STORAGE_ADAPTER', StorageDriver::LOCAL),
        drivers: [
            StorageDriver::LOCAL => new LocalFilesystemConfigStruct(path('/storage/app')),
            StorageDriver::S3 => new S3FilesystemConfigStruct(
                client: [
                    'credentials' => [
                        'key' => (string)env('SALT_AWS_S3_ACCESS_KEY_ID'),
                        'secret' => (string)env('SALT_AWS_S3_SECRET_ACCESS_KEY'),
                    ],
                    'region' => (string)env('SALT_AWS_S3_DEFAULT_REGION', 'us-west-1'),
                    'signature' => 'v4',
                    'version' => 'latest',
                ],
                bucket_name: (string)env('SALT_AWS_S3_BUCKET_NAME'),
                prefix: (string)env('SALT_AWS_S3_PATH_PREFIX'),
            ),
        ],
    ),
];
