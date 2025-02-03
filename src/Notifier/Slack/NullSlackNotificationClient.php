<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier\Slack;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use Psr\Log\LoggerInterface;

final readonly class NullSlackNotificationClient implements SlackNotificationClient
{
    public function __construct(
        private LoggerInterface $logger,
        public string $channel = '',
    ) {
    }

    #[\Override]
    public function send(SlackNotification $notification, Ttl|null $ttl = null): bool
    {
        $this->logger->debug('Slack Message Dispatched', [
            'text' => $notification->message,
            'channel' => $notification->channel ?? $this->channel,
        ]);

        return true;
    }
}
