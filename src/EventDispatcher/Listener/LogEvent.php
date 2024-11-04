<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\Listener;

use Psr\Log\LoggerInterface;

class LogEvent
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(object $event): void
    {
        try {
            $serialized = \serialize($event);
        } catch (\Throwable $e) {
            $serialized = 'Unable to serialize event: ' . $e->getMessage();
        }

        $this->logger->debug('Event Dispatched: ' . $event::class, [
            'event' => $serialized,
        ]);
    }
}
