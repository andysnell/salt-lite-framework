<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionProvider;
use PhoneBurner\SaltLite\Framework\Util\Helper\Reflect;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection as DoctrineTransportConnection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

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
        return Reflect::ghost(DoctrineTransport::class, function (DoctrineTransport $ghost) use ($connection, $options): void {
            $ghost->__construct(new DoctrineTransportConnection(
                $options,
                $this->connection_provider->getConnection($connection),
            ), $this->serializer);
        });
    }
}
