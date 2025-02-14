<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener;

use PhoneBurner\SaltLite\Framework\EventDispatcher\LoggableEvent;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Psr\Log\LoggerInterface;

class LogEventWasDispatched
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(object $event): void
    {
        if ($event instanceof LoggableEvent) {
            $this->logger->log($event->getLogLevel(), $event->getLogMessage(), $event->getLogContext());
            return;
        }

        try {
            $message = Str::shortname($event::class);
            $message = Str::ucwords($message);
            if (\str_ends_with($message, ' Event')) {
                $message = \substr($message, 0, -6);
            }

            $this->logger->debug($message, [
                'class' => $event::class,
                'event' => $event,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to log event', [
                'class' => $event::class,
                'exception' => $e,
            ]);
        }
    }
}
