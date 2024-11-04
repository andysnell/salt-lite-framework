<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Queue;

use PhoneBurner\SaltLite\Framework\MessageBus\MessageBus;

/**
 * @api
 * @internal
 */
class MessageBusJobDispatcher implements JobDispatcher
{
    public function __construct(
        private readonly MessageBus $bus,
    ) {
    }

    #[\Override]
    public function dispatch(Job $job): void
    {
        $this->bus->dispatch($job);
    }
}
