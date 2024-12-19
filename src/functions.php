<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework;

use PhoneBurner\SaltLite\Framework\App\App;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

function app(): App
{
    return App::instance();
}

/**
 * Get an environment variable allowing for default.
 * Note: this has slightly different behavior from SALT, as it does not check $_SERVER
 * or fall back to getenv() if the variable is not set in $_ENV. The Salt-Lite
 * Framework assumes that all environment variables are set in $_ENV.
 */
function env(
    string $key,
    mixed $production = null,
    mixed $development = null,
    mixed $integration = null,
): mixed {
    return App::instance()->environment->env($key, $production, $development, $integration);
}

/**
 * Get full path relative to the application root
 *
 * @return non-empty-string
 */
function path(string $path): string
{
    \assert(\defined('\PhoneBurner\SaltLite\Framework\APP_ROOT'), 'APP_ROOT must be defined');
    $path = APP_ROOT . $path;
    \assert($path !== '');

    return $path;
}
