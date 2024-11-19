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
 *
 * Preserves existing (unintended) application behavior where Phinx < 0.16.0
 * which had a dependency on CakePHP 2.x, which defined the \env() function
 * with slightly different behavior than the one defined here, and which
 * would always be defined first. Note that this may have some weird behavior
 * with `null` values in $_ENV, but actually defined in the environment, so
 * that an empty string is returned instead of the default value.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_SERVER[$key] ?? $_ENV[$key] ?? null;
    if ($value === null && \getenv($key) !== false) {
        $value = (string)\getenv($key);
    }

    return $value ?? $default;
}

/** Get full path relative to the application root */
function path(string $path): string
{
    \assert(\defined('\PhoneBurner\SaltLite\Framework\APP_ROOT'), 'APP_ROOT must be defined');
    return APP_ROOT . $path;
}
