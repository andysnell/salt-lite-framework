<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Event;

class InvokableMessageHandlingStarting
{
    public function __construct(public readonly object $message)
    {
    }
}
