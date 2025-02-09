<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener;

use Psr\Log\LoggerInterface;

class LogEventWasDispatched
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(object $event): void
    {
        try {
            $serialized = \serialize($event);
        } catch (\Throwable) {
            $serialized = null;
        }

        $this->logger->debug('Event Dispatched: ' . $event::class, [
            'event' => $serialized,
        ]);
    }
}
