<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier\Slack;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Notifier\NotifierServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;

#[DefaultServiceProvider(NotifierServiceProvider::class)]
interface SlackNotificationClient
{
    public function send(SlackNotification $notification, Ttl|null $ttl = null): bool;
}
