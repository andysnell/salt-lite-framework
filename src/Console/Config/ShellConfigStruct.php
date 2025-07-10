<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\Console\Command\InteractiveSaltShellCommand;

final readonly class ShellConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param array<string, class-string> $services Map of variable names to service class-strings to inject into the shell
     * @param list<class-string> $imports Application Imports to inject into the shell
     * @param array<string,mixed> $options Configuration Options for PsySH
     * @link https://github.com/bobthecow/psysh/wiki/Config-options
     */
    public function __construct(
        public array $services = [],
        public array $imports = [],
        public array $options = InteractiveSaltShellCommand::DEFAULT_PSYSH_OPTIONS,
    ) {
    }
}
