<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Mailer\TransportDriver;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'mailer' => [
        'default_from_address' => env('SALT_MAILER_DEFAULT_FROM_ADDRESS', 'nobody@example.com'),
        'default_driver' => env('SALT_MAILER_DRIVER', TransportDriver::None->value, TransportDriver::Smtp->value),
        'async' => (bool)env('SALT_MAILER_ASYNC', true),
        'drivers' => [
            TransportDriver::None->value => [],
            TransportDriver::Smtp->value => [
                'host' => env('SALT_SMTP_HOST', development: 'mailhog'),
                'port' => env('SALT_SMTP_PORT', development: 1025),
                'user' => env('SALT_SMTP_USER', development: 'foo'),
                'pass' => env('SALT_SMTP_PASS', development: 'bar'),
                'encryption' => (bool)env('SALT_SMTP_SECURITY', true, false),
                ],
            TransportDriver::SendGrid->value => [
                'api_key' => env('SALT_SENDGRID_API_KEY'),
            ],
        ],
    ],
];
