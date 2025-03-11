<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;

class AmqpTransportFactory
{
    public function make(string $connection, array $options): AmqpTransport
    {
        throw new \LogicException('Not implemented');
    }
}
