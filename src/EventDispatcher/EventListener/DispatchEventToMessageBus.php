<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener;

use PhoneBurner\SaltLite\MessageBus\MessageBus;

class DispatchEventToMessageBus
{
    public function __construct(private readonly MessageBus $message_bus)
    {
    }

    public function __invoke(object $event): void
    {
        $this->message_bus->dispatch($event);
    }
}
