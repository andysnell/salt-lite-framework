<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier\Slack;

class SlackNotification
{
    public function __construct(
        public string $message,
        public string|null $channel = null,
    ) {
    }
}
