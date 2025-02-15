<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\App\Event\ApplicationBootstrap;
use PhoneBurner\SaltLite\Framework\App\Event\ApplicationTeardown;
use PhoneBurner\SaltLite\Framework\Console\EventListener\ConsoleErrorListener;
use PhoneBurner\SaltLite\Framework\Logging\LogLevel;
use PhoneBurner\SaltLite\Framework\MessageBus\Event\InvokableMessageHandlingComplete;
use PhoneBurner\SaltLite\Framework\MessageBus\Event\InvokableMessageHandlingFailed;
use PhoneBurner\SaltLite\Framework\MessageBus\Event\InvokableMessageHandlingStarting;
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
use Symfony\Component\Messenger\EventListener\AddErrorDetailsStampListener;
use Symfony\Component\Messenger\EventListener\DispatchPcntlSignalListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnCustomStopExceptionListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Scheduler\Event\FailureEvent;
use Symfony\Component\Scheduler\Event\PostRunEvent;
use Symfony\Component\Scheduler\Event\PreRunEvent;
use Symfony\Component\Scheduler\EventListener\DispatchSchedulerEventListener;

return [
    'event_dispatcher' => [
        'event_dispatch_log_level' => LogLevel::Debug, // set to null to disable
        'event_failure_log_level' => LogLevel::Warning, // set to null to disable
        'listeners' => [
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
    ],
];
