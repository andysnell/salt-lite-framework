<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Console\EventListener\ConsoleErrorListener;
use PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener\LogEventWasDispatched;
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
        'listeners' => [
            // Message Bus Events
            SendMessageToTransportsEvent::class => [],
            WorkerStartedEvent::class => [],
            WorkerRunningEvent::class => [],
            WorkerStoppedEvent::class => [],
            WorkerMessageReceivedEvent::class => [
                LogEventWasDispatched::class,
            ],
            WorkerMessageHandledEvent::class => [
                LogEventWasDispatched::class,
            ],
            WorkerMessageSkipEvent::class => [],
            WorkerMessageFailedEvent::class => [
                LogWorkerMessageFailedEvent::class,
            ],
            WorkerMessageRetriedEvent::class => [
                LogEventWasDispatched::class,
            ],
            WorkerRateLimitedEvent::class => [],

            // Scheduler Events
            PreRunEvent::class => [
                LogEventWasDispatched::class,
            ],

            PostRunEvent::class => [
                LogEventWasDispatched::class,
            ],

            FailureEvent::class => [
                LogEventWasDispatched::class,
            ],

            // Queue Job Events
            InvokableMessageHandlingStarting::class => [
                LogEventWasDispatched::class,
            ],
            InvokableMessageHandlingComplete::class => [
                LogEventWasDispatched::class,
            ],
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
        'subscribers' => [
            // Framework Subscribers
            // Console Subscribers
            ConsoleErrorListener::class,

            // Messenger Subscribers
            AddErrorDetailsStampListener::class,
            DispatchPcntlSignalListener::class,
            SendFailedMessageForRetryListener::class,
            SendFailedMessageToFailureTransportListener::class,
            StopWorkerOnCustomStopExceptionListener::class,
            StopWorkerOnRestartSignalListener::class,

            // Scheduler Subscribers
            DispatchSchedulerEventListener::class,
        ],
    ],
];
