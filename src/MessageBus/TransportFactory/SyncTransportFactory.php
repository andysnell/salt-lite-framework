<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\Framework\MessageBus\Config\TransportConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\MessageBusContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;
use PhoneBurner\SaltLite\MessageBus\MessageBus;
use PhoneBurner\SaltLite\Type\Type;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class SyncTransportFactory implements TransportFactory
{
    public function __construct(private readonly MessageBusContainer $message_bus_locator)
    {
    }

    public function make(TransportConfigStruct $config): TransportInterface
    {
        \assert($config->class === SyncTransport::class);

        return new SyncTransport(Type::of(
            MessageBusInterface::class,
            $this->message_bus_locator->get($config->options['bus'] ?? MessageBus::DEFAULT),
        ));
    }
}
