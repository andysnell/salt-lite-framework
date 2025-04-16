<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionProvider;
use PhoneBurner\SaltLite\Framework\MessageBus\Config\TransportConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as DoctrineTransportConnection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

class DoctrineTransportFactory implements TransportFactory
{
    public function __construct(
        private readonly ConnectionProvider $connection_provider,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function make(TransportConfigStruct $config): DoctrineTransport
    {
        \assert($config->class === DoctrineTransport::class);

        $options = $config->options;
        $options['auto_setup'] = false; // disable auto setup
        return ghost(fn(DoctrineTransport $ghost): null => $ghost->__construct(
            /** @phpstan-ignore new.internalClass */
            new DoctrineTransportConnection(
                $options,
                $this->connection_provider->getConnection($config->connection),
            ),
            $this->serializer,
        ));
    }
}
