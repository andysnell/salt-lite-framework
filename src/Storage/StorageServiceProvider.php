<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Storage;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\FilesystemWriter;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;

#[Internal('Override Definitions in Application Service Providers')]
class StorageServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->bind(FilesystemReader::class, FilesystemOperator::class);
        $container->bind(FilesystemWriter::class, FilesystemOperator::class);
        $container->set(
            FilesystemOperator::class,
            static function (ContainerInterface $container): FilesystemOperator {
                return $container->get(FilesystemOperatorFactory::class)->default();
            },
        );

        $container->set(
            FilesystemOperatorFactory::class,
            static function (ContainerInterface $container): FilesystemOperatorFactory {
                return new FilesystemOperatorFactory($container->get(Configuration::class));
            },
        );
    }
}
