<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier\Slack;

use PhoneBurner\SaltLite\Time\Ttl;

interface SlackNotificationClient
{
    public function send(SlackNotification $notification, Ttl|null $ttl = null): bool;
}
