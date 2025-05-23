<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Storage\FilesystemAdapterFactory;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Framework\Storage\Config\LocalFilesystemConfigStruct;
use PhoneBurner\SaltLite\Framework\Storage\FilesystemAdapterFactory;

class LocalFilesystemAdapterFactory implements FilesystemAdapterFactory
{
    public function make(ConfigStruct $config): FilesystemAdapter
    {
        \assert($config instanceof LocalFilesystemConfigStruct);

        return new LocalFilesystemAdapter(
            location: $config->location,
            visibility: $config->visibility,
            writeFlags: $config->write_flags,
            linkHandling: $config->link_handling,
            mimeTypeDetector: $config->mime_type_detector,
            lazyRootCreation: $config->lazy_root_creation,
            useInconclusiveMimeTypeFallback: $config->use_inconclusive_mime_type_fallback,
        );
    }
}
