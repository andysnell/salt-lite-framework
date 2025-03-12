<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Logging\LogEntry;
use PhoneBurner\SaltLite\Logging\Loggable;
use PhoneBurner\SaltLite\Logging\LogLevel;
use PhoneBurner\SaltLite\String\Str;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymfonyEventDispatcherAdapter implements SymfonyEventDispatcherInterface
{
    /**
     * @param LogLevel|null $event_log_level Set to null to disable logging of all dispatched events.
     * @param LogLevel|null $failure_log_level Set to null to disable logging of all dispatch failures.
     */
    public function __construct(
        private readonly SymfonyEventDispatcherInterface $dispatcher,
        private readonly LoggerInterface $logger,
        private readonly LogLevel|null $event_log_level = LogLevel::Debug,
        private readonly LogLevel|null $failure_log_level = LogLevel::Warning,
    ) {
    }

    public function dispatch(object $event, string|null $event_name = null): object
    {
        try {
            if ($this->event_log_level instanceof LogLevel || $event instanceof Loggable) {
                $this->logEventWasDispatched($event, $this->event_log_level ?? LogLevel::Debug);
            }

            return $this->dispatcher->dispatch($event);
        } catch (\Throwable $e) {
            $this->logger->log($this->failure_log_level, 'Failed to Dispatch Event', [
                'event' => $event::class,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function addListener(string $event_name, callable $listener, int $priority = 0): void
    {
        $this->dispatcher->addListener($event_name, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    public function removeListener(string $event_name, callable $listener): void
    {
        $this->dispatcher->removeListener($event_name, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->dispatcher->removeSubscriber($subscriber);
    }

    public function getListeners(string|null $event_name = null): array
    {
        return $this->dispatcher->getListeners($event_name);
    }

    public function getListenerPriority(string $event_name, callable $listener): int|null
    {
        return $this->dispatcher->getListenerPriority($event_name, $listener);
    }

    public function hasListeners(string|null $event_name = null): bool
    {
        return $this->dispatcher->hasListeners($event_name);
    }

    /**
     * By default, creates debug-level log message with the unqualified event
     * class name in title case, e.g.\PhoneBurner\SaltLite\MessageBus\Event\InvokableMessageHandlingStarting
     * would become "Invokable Message Handling Starting".
     */
    private function logEventWasDispatched(object $event, LogLevel $log_level): void
    {
        try {
            $log_entry = $event instanceof Loggable ? $event->getLogEntry() : new LogEntry($log_level, context: [
                'event' => $event::class,
            ]);

            $this->logger->log(
                $log_entry->level,
                $log_entry->message ?: Str::ucwords(Str::shortname($event::class)),
                $log_entry->context,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Failed to Log Event Was Dispatched', [
                'class' => $event::class,
                'exception' => $e,
            ]);
        }
    }
}
