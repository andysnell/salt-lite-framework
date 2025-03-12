<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Storage;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\FilesystemWriter;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Container\DeferrableServiceProvider;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class StorageServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            FilesystemReader::class,
            FilesystemWriter::class,
            FilesystemOperator::class,
            FilesystemOperatorFactory::class,
        ];
    }

    public static function bind(): array
    {
        return [
            FilesystemReader::class => FilesystemOperator::class,
            FilesystemWriter::class => FilesystemOperator::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            FilesystemOperator::class,
            static fn(App $app): FilesystemOperator => $app->get(FilesystemOperatorFactory::class)->default(),
        );

        $app->set(
            FilesystemOperatorFactory::class,
            static fn(App $app): FilesystemOperatorFactory => new FilesystemOperatorFactory($app->config),
        );
    }
}
