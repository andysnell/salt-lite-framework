<?php

declare(strict_types=1);

// This constant must be defined before including the vendor autoload file so that
// it is defined before the SaltLite\Framework constants are defined in the
// application bootstrap file.
use PhoneBurner\SaltLite\Framework\App\Context;

define('PhoneBurner\SaltLite\Framework\PASSWORD_ARGON2_OPTIONS', [
    'memory_cost' => 32,
    'time_cost' => 1,
    'thread_cost' => 1,
]);

define('PhoneBurner\SaltLite\Framework\CONTEXT', Context::Test);

define('PhoneBurner\SaltLite\Framework\UNIT_TEST_ROOT', __DIR__);

defined('PhoneBurner\SaltLite\Framework\APP_ROOT') || define('PhoneBurner\SaltLite\Framework\APP_ROOT', dirname(__DIR__));
