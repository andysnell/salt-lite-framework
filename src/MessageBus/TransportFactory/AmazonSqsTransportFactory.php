<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransport;

class AmazonSqsTransportFactory
{
    public function make(array $options): AmazonSqsTransport
    {
        throw new \Exception('Not implemented');
    }
}
