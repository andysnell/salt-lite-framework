<?php

/**
 * Application configuration.
 *
 * This would be the place to define any configuration settings that are specific
 * to your application. This file is included in the application bootstrap process,
 * along with the other configuration files
 */

declare(strict_types=1);

use PhoneBurner\SaltLite\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\App\Config\AppConfigStruct;
use PhoneBurner\SaltLite\I18n\IsoLocale;
use PhoneBurner\SaltLite\Time\TimeZone\Tz;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'app' => new AppConfigStruct(
        name: 'SaltLite Framework',
        key: SharedKey::import(env('SALT_APP_KEY')),
        timezone: Tz::Utc,
        locale: IsoLocale::EN_US,
    ),
];
