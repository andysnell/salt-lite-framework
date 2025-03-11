<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransport;

class AmazonSqsTransportFactory
{
    public function make(string $connection, array $options): AmazonSqsTransport
    {
        throw new \LogicException('Not implemented');
    }
}
