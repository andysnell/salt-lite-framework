<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Container;

use PhoneBurner\SaltLite\Framework\Container\ObjectContainer\MutableObjectContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\MessageBus;
use PhoneBurner\SaltLite\Framework\MessageBus\SymfonyMessageBusAdapter;

/**
 * @extends MutableObjectContainer<SymfonyMessageBusAdapter>
 */
class MessageBusContainer extends MutableObjectContainer
{
    public function default(): SymfonyMessageBusAdapter
    {
        return $this->entries[MessageBus::DEFAULT] ?? throw new \RuntimeException(
            \sprintf('No default message bus ("%s") found', MessageBus::DEFAULT),
        );
    }
}
