<?php

declare(strict_types=1);

use function PhoneBurner\SaltLite\Framework\env;

return [
    'notifier' => [
        'slack' => [
            'endpoint' => 'https://hooks.slack.com/services/' . env('SALT_SLACK_API_KEY'),
            'default_options' => [
                'username' => 'monitor',
                'channel' => env('SALT_SLACK_DEFAULT_CHANNEL'),
                'link_names' => true,
            ],
        ],
    ],
];
