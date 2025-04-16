<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\Framework\MessageBus\Config\TransportConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransport;

class AmazonSqsTransportFactory implements TransportFactory
{
    public function make(TransportConfigStruct $config): AmazonSqsTransport
    {
        \assert($config->class === AmazonSqsTransport::class);
        throw new \LogicException('Not implemented');
    }
}
