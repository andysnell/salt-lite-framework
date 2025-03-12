<?php

/**
 * This bootstrap file is loaded after the vendor autoload files, and after the
 * XML configuration file has been loaded, but before tests are run.
 */

declare(strict_types=1);

use PhoneBurner\SaltLite\App\Context;

\defined('PhoneBurner\SaltLite\Framework\CONTEXT')
|| \define('PhoneBurner\SaltLite\Framework\CONTEXT', Context::Test);

\defined('PhoneBurner\SaltLite\Framework\UNIT_TEST_ROOT')
|| \define('PhoneBurner\SaltLite\Framework\UNIT_TEST_ROOT', __DIR__);
