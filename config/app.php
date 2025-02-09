<?php

/**
 * Application configuration.
 *
 * This would be the place to define any configuration settings that are specific
 * to your application. This file is included in the application bootstrap process,
 * along with the other configuration files
 */

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\Tz;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'app' => [
        'name' => 'Salt-Lite Framework',
        'timezone' => Tz::Utc->value,
        'locale' => 'en_US',
        'fallback_locale' => 'en_US',
        'key' => env('SALT_APP_KEY'),
    ],
];
