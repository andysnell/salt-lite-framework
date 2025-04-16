<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Storage\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\Storage\FilesystemAdapterFactory;
use PhoneBurner\SaltLite\Framework\Storage\StorageDriver;

final readonly class StorageConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param StorageDriver::*|non-empty-string $default
     * @param array<StorageDriver::*|string, ConfigStruct> $drivers
     * @param array<class-string<ConfigStruct>, class-string<FilesystemAdapterFactory>> $factories
     * Map the configuration struct for a driver to the custom factory to use to create it.
     * The factory will be resolved from the container, so be sure to register it.
     */
    public function __construct(
        public string $default = StorageDriver::LOCAL,
        public array $drivers = [],
        public array $factories = [],
    ) {
    }
}
