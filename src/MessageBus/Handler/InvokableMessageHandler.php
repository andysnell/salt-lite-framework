<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Handler;

use PhoneBurner\SaltLite\Framework\Container\InvokingContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\Attribute\MessageHandler;
use PhoneBurner\SaltLite\Framework\MessageBus\Event\InvokableMessageHandlingComplete;
use PhoneBurner\SaltLite\Framework\MessageBus\Event\InvokableMessageHandlingFailed;
use PhoneBurner\SaltLite\Framework\MessageBus\Event\InvokableMessageHandlingStarting;
use PhoneBurner\SaltLite\Framework\MessageBus\MessageBusServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use Psr\EventDispatcher\EventDispatcherInterface;

#[DefaultServiceProvider(MessageBusServiceProvider::class)]
#[MessageHandler]
class InvokableMessageHandler
{
    public function __construct(
        private readonly InvokingContainer $container,
        private readonly EventDispatcherInterface $event_dispatcher,
    ) {
    }

    public function __invoke(object $message): void
    {
        try {
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingStarting($message));
            $this->container->call($message);
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingComplete($message));
        } catch (\Throwable $e) {
            $this->event_dispatcher->dispatch(new InvokableMessageHandlingFailed($message, $e));
            throw $e;
        }
    }
}
