<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\App\Event\ApplicationBootstrap;
use PhoneBurner\SaltLite\App\Event\ApplicationTeardown;
use PhoneBurner\SaltLite\Framework\EventDispatcher\Config\EventDispatcherConfigStruct;
use PhoneBurner\SaltLite\Logging\LogLevel;
use PhoneBurner\SaltLite\MessageBus\Event\InvokableMessageHandlingComplete;
use PhoneBurner\SaltLite\MessageBus\Event\InvokableMessageHandlingFailed;
use PhoneBurner\SaltLite\MessageBus\Event\InvokableMessageHandlingStarting;
use PhoneBurner\SaltLite\Framework\MessageBus\EventListener\LogFailedInvokableMessageHandlingAttempt;
use PhoneBurner\SaltLite\Framework\MessageBus\EventListener\LogWorkerMessageFailedEvent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageRetriedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageSkipEvent;
use Symfony\Component\Messenger\Event\WorkerRateLimitedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Event\WorkerStoppedEvent;
use Symfony\Component\Scheduler\Event\FailureEvent;
use Symfony\Component\Scheduler\Event\PostRunEvent;
use Symfony\Component\Scheduler\Event\PreRunEvent;

return [
    'event_dispatcher' => new EventDispatcherConfigStruct(
        event_dispatch_log_level: LogLevel::Debug,
        event_failure_log_level: LogLevel::Warning,
        listeners: [
            // Application Lifecycle Events
            ApplicationBootstrap::class => [],
            ApplicationTeardown::class => [],

            // Message Bus Events
            SendMessageToTransportsEvent::class => [],
            WorkerStartedEvent::class => [],
            WorkerRunningEvent::class => [],
            WorkerStoppedEvent::class => [],
            WorkerMessageReceivedEvent::class => [],
            WorkerMessageHandledEvent::class => [],
            WorkerMessageSkipEvent::class => [],
            WorkerMessageFailedEvent::class => [
                LogWorkerMessageFailedEvent::class,
            ],
            WorkerMessageRetriedEvent::class => [],
            WorkerRateLimitedEvent::class => [],

            // Scheduler Events
            PreRunEvent::class => [],
            PostRunEvent::class => [],
            FailureEvent::class => [],

            // Queue Job Events
            InvokableMessageHandlingStarting::class => [],
            InvokableMessageHandlingComplete::class => [],
            InvokableMessageHandlingFailed::class => [
                LogFailedInvokableMessageHandlingAttempt::class,
            ],

            // Console Events
            ConsoleCommandEvent::class => [],
            ConsoleErrorEvent::class => [],
            ConsoleSignalEvent::class => [],
            ConsoleTerminateEvent::class => [],

            // Application Events & Listeners
        ],
    ),
];
