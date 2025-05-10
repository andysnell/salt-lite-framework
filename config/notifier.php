<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Notifier\Config\NotifierConfigStruct;
use PhoneBurner\SaltLite\Framework\Notifier\Slack\Config\SlackWebhookNotifierConfigStruct;

use function PhoneBurner\SaltLite\Framework\env;

return [
    'notifier' => new NotifierConfigStruct([
        'slack_webhooks' => new SlackWebhookNotifierConfigStruct(
            endpoint: (string)env('SALT_SLACK_WEBHOOK_URL'),
            options: [
                'username' => 'SaltLite',
                'channel' => (string)env('SALT_SLACK_DEFAULT_CHANNEL'),
                'link_names' => true,
            ],
        ),
    ]),
];
