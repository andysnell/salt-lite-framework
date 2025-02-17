<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Mailer\Config\SendgridDriverConfigStruct;
use PhoneBurner\SaltLite\Framework\Mailer\Config\SmtpDriverConfigStruct;
use PhoneBurner\SaltLite\Framework\Mailer\Transport\TransportDriver;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'mailer' => [
        'default_from_address' => env('SALT_MAILER_DEFAULT_FROM_ADDRESS', 'donotreply@example.com'),
        'default_driver' => env('SALT_MAILER_DRIVER', TransportDriver::None, TransportDriver::Smtp),
        'async' => (bool)env('SALT_MAILER_ASYNC', true),
        'drivers' => [
            TransportDriver::Smtp->value => new SmtpDriverConfigStruct(
                host: env('SALT_SMTP_HOST', development: 'mailhog'),
                port: env('SALT_SMTP_PORT', development: 1025),
                user: env('SALT_SMTP_USER', development: 'foo'),
                password: env('SALT_SMTP_PASS', development: 'bar'),
                encryption: (bool)env('SALT_SMTP_SECURITY', true, false),
            ),
            TransportDriver::SendGrid->value => new SendgridDriverConfigStruct(env('SALT_SENDGRID_API_KEY')),
        ],
    ],
];
