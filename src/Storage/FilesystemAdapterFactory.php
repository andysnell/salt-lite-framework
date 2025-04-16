<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Storage;

use League\Flysystem\FilesystemAdapter;
use PhoneBurner\SaltLite\Configuration\ConfigStruct;

interface FilesystemAdapterFactory
{
    public function make(ConfigStruct $config): FilesystemAdapter;
}
