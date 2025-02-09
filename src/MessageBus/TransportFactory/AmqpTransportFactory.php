<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;

class AmqpTransportFactory
{
    public function make(array $options): AmqpTransport
    {
        throw new \Exception('Not implemented');
    }
}
