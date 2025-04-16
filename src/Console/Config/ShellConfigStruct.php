<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use Psy\Configuration;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

final readonly class ShellConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public const array DEFAULT_PSYSH_OPTIONS = [
        'commands' => [],
        'configDir' => APP_ROOT . '/build/psysh/config',
        'dataDir' => APP_ROOT . '/build/psysh/data',
        'defaultIncludes' => [],
        'eraseDuplicates' => true,
        'errorLoggingLevel' => \E_ALL,
        'forceArrayIndexes' => true,
        'historySize' => 1000,
        'runtimeDir' => APP_ROOT . '/build/psysh/tmp',
        'updateCheck' => 'never',
        'useBracketedPaste' => true,
        'verbosity' => Configuration::VERBOSITY_NORMAL,
    ];

    /**
     * @param list<class-string> $services Application Services to inject into the shell
     * @param list<class-string> $imports Application Imports to inject into the shell
     * @param array<string,mixed> $psysh Configuration for PsySH
     * @link https://github.com/bobthecow/psysh/wiki/Config-options
     */
    public function __construct(
        public array $services = [],
        public array $imports = [],
        public array $psysh = self::DEFAULT_PSYSH_OPTIONS,
    ) {
    }
}
