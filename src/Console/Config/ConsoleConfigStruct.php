<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

final readonly class ConsoleConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public function __construct(
        public array $commands = [],
        public ShellConfigStruct $shell = new ShellConfigStruct(),
    ) {
    }
}
