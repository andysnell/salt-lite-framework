<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Storage;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Exception\InvalidConfiguration;

class FilesystemOperatorFactory
{
    private array $cache = [];

    public function __construct(private readonly Configuration $configuration)
    {
    }

    public function make(string $driver): FilesystemOperator
    {
        return $this->cache[$driver] ??= $this->createFilesystemOperator($driver);
    }

    public function default(): FilesystemOperator
    {
        return $this->cache['default'] ??= $this->make($this->configuration->get('storage.default'));
    }

    private function createFilesystemOperator(string $driver): FilesystemOperator
    {
        $config = $this->configuration->get('storage.drivers.' . $driver);
        if (! $config || ! \is_array($config)) {
            throw new InvalidConfiguration("No configuration defined for driver: $driver");
        }

        return match ($config['adapter'] ?? null) {
            LocalFilesystemAdapter::class => $this->createLocalFilesystemOperator($config),
            AwsS3V3Adapter::class => $this->createS3FilesystemOperator($config),
            default => throw new \UnexpectedValueException("Unsupported adapter for : $driver"),
        };
    }

    private function createLocalFilesystemOperator(array $config): FilesystemOperator
    {
        return new Filesystem(new LocalFilesystemAdapter(
            $config['root'] ?? throw new InvalidConfiguration('Missing root configuration for local adapter'),
        ));
    }

    private function createS3FilesystemOperator(array $config): FilesystemOperator
    {
        return new Filesystem(new AwsS3V3Adapter(
            new S3Client($config['client']),
            $config['bucket-name'],
            $config['prefix'] ?? '',
        ));
    }
}
