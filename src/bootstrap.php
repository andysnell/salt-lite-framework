<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\App\ErrorReporting;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

\define('PhoneBurner\SaltLite\Framework\START_MICROTIME', \microtime(true));
\define('PhoneBurner\SaltLite\Framework\APP_ROOT', \dirname(__DIR__, \str_contains(__DIR__, 'vendor') ? 4 : 1));
\define('PhoneBurner\SaltLite\Framework\WEB_ROOT', APP_ROOT . '/public');

// Make sure that the build stage is defined and set the same on $_SERVER and $_ENV,
// If not explicitly set, default to production.
$build_stage = BuildStage::instance($_SERVER['SALT_BUILD_STAGE'] ?? $_ENV['SALT_BUILD_STAGE'] ?? BuildStage::Production);
$_SERVER['SALT_BUILD_STAGE'] = $build_stage->value;
$_ENV['SALT_BUILD_STAGE'] = $build_stage->value;

if ($build_stage !== BuildStage::Production) {
    // Override the error reporting settings based on the environment configuration.
    ErrorReporting::override($_ENV);

    // Check if we're running in a PHPUnit context.
    if (\defined('PHPUNIT_COMPOSER_INSTALL') || \defined('BEHAT_BIN_PATH')) {
        define('PhoneBurner\SaltLite\Framework\CONTEXT', Context::Test);
        define('PhoneBurner\SaltLite\Framework\PASSWORD_ARGON2_OPTIONS', [
            'memory_cost' => 32,
            'time_cost' => 1,
            'thread_cost' => 1,
        ]);
    }
} else {
    \define('PhoneBurner\SaltLite\Framework\PASSWORD_ARGON2_OPTIONS', [
        'memory_cost' => \PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
        'time_cost' => \PASSWORD_ARGON2_DEFAULT_TIME_COST,
        'thread_cost' => \PASSWORD_ARGON2_DEFAULT_THREADS,
    ]);
}
