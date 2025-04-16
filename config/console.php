<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Console\Config\ConsoleConfigStruct;
use PhoneBurner\SaltLite\Framework\Console\Config\ShellConfigStruct;

return [
    'console' => new ConsoleConfigStruct(
        commands: [],
        shell: new ShellConfigStruct(),
    ),
];
