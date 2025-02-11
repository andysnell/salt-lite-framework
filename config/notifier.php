<?php

declare(strict_types=1);

use function PhoneBurner\SaltLite\Framework\env;

return [
    'notifier' => [
        'slack_webhooks' => [
            'endpoint' => (string)env('SALT_SLACK_WEBHOOK_URL'),
            'default_options' => [
                'username' => 'monitor',
                'channel' => env('SALT_SLACK_DEFAULT_CHANNEL'),
                'link_names' => true,
            ],
        ],
    ],
];
