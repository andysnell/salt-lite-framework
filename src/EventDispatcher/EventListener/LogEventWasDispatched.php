<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener;

use PhoneBurner\SaltLite\Logging\LogEntry;
use PhoneBurner\SaltLite\Logging\Loggable;
use PhoneBurner\SaltLite\String\Str;
use Psr\Log\LoggerInterface;

/**
 * A listener to attach to an event to log that the specific event was dispatched.
 * For more general logging, use the configuration settings in EventDispatcherConfigStruct
 * to toggle event logging.
 */
class LogEventWasDispatched
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(object $event): void
    {
        try {
            $log_entry = $this->createLogEntry($event);
            $this->logger->log($log_entry->level, $log_entry->message, $log_entry->context);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to Log Event Was Dispatched', [
                'class' => $event::class,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Returns a debug log message with the unqualified event class name in
     * title case, e.g.\PhoneBurner\SaltLite\MessageBus\Event\InvokableMessageHandlingStarting
     * would become "Invokable Message Handling Starting".
     */
    private function createLogEntry(object $event): LogEntry
    {
        if ($event instanceof Loggable) {
            return $event->getLogEntry();
        }

        return new LogEntry(message: Str::ucwords(Str::shortname($event::class)), context: [
            'event' => $event::class,
        ]);
    }
}
