<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Event;

class InvokableMessageHandlingComplete
{
    public function __construct(public readonly object $message)
    {
    }
}
