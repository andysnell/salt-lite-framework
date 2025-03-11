<?php

/**
 * Application configuration.
 *
 * This would be the place to define any configuration settings that are specific
 * to your application. This file is included in the application bootstrap process,
 * along with the other configuration files
 */

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\App\Config\AppConfigStruct;
use PhoneBurner\SaltLite\Framework\Domain\I18n\IsoLocale;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\Tz;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'app' => new AppConfigStruct(
        name: 'Salt-Lite Framework',
        key: SharedKey::import(env('SALT_APP_KEY')),
        timezone: Tz::Utc,
        locale: IsoLocale::EN_US,
    ),
];
