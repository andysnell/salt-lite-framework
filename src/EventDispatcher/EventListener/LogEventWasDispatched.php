<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener;

use PhoneBurner\SaltLite\Framework\EventDispatcher\EventDispatcherServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\LogEntry;
use PhoneBurner\SaltLite\Framework\Logging\Loggable;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Psr\Log\LoggerInterface;

#[DefaultServiceProvider(EventDispatcherServiceProvider::class)]
class LogEventWasDispatched
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $log_all_events = false,
    ) {
    }

    public function __invoke(object $event): void
    {
        try {
            match (true) {
                $event instanceof Loggable => $this->log($event->getLogEntry()),
                $this->log_all_events => $this->log($this->createLogEntry($event)),
                default => null,
            };
        } catch (\Throwable $e) {
            $this->logger->error('Failed to Log Event Was Dispatched', [
                'class' => $event::class,
                'exception' => $e,
            ]);
        }
    }

    private function log(LogEntry $log_entry): void
    {
        $this->logger->log($log_entry->level, $log_entry->message, $log_entry->context);
    }

    /**
     * Returns a debug log message with the unqualified event class name in
     * title case, e.g.\PhoneBurner\SaltLite\Framework\MessageBus\Event\InvokableMessageHandlingStarting
     * would become "Invokable Message Handling Starting".
     */
    private function createLogEntry(object $event): LogEntry
    {
        return new LogEntry(message: Str::ucwords(Str::shortname($event::class)), context: [
            'event' => $event::class,
        ]);
    }
}
