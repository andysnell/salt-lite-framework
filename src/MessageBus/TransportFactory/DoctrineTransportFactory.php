<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionProvider;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as DoctrineTransportConnection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

class DoctrineTransportFactory
{
    public function __construct(
        private readonly ConnectionProvider $connection_provider,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function make(string $connection, array $options): DoctrineTransport
    {
        $options['auto_setup'] = false; // disable auto setup
        return ghost(fn(DoctrineTransport $ghost): null => $ghost->__construct(
            new DoctrineTransportConnection(
                $options,
                $this->connection_provider->getConnection($connection),
            ),
            $this->serializer,
        ));
    }
}
