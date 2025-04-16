<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\Framework\MessageBus\Config\TransportConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;

class AmqpTransportFactory implements TransportFactory
{
    public function make(TransportConfigStruct $config): AmqpTransport
    {
        \assert($config->class === AmqpTransport::class);
        throw new \LogicException('Not implemented');
    }
}
