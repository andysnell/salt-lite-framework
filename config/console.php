<?php

declare(strict_types=1);

use Psy\Configuration;

use function PhoneBurner\SaltLite\Framework\path;

return [
    'console' => [
        'commands' => [
            // Register Application Commands
        ],
        'shell' => [
            'services' => [
                // Register Application Services to inject into the shell
            ],
            'imports' => [
                // Register Application Imports to inject into the shell
            ],
            'psysh' => [
                'commands' => [],
                'configDir' => path('/build/psysh/config'),
                'dataDir' => path('/build/psysh/data'),
                'defaultIncludes' => [],
                'eraseDuplicates' => true,
                'errorLoggingLevel' => \E_ALL,
                'forceArrayIndexes' => true,
                'historySize' => 1000,
                'runtimeDir' => path('/build/psysh/tmp'),
                'updateCheck' => 'never',
                'useBracketedPaste' => true,
                'verbosity' => Configuration::VERBOSITY_NORMAL,
            ],
        ],
    ],
];
