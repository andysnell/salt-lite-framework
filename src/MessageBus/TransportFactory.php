<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionFactory;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\MessageBusContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\AmazonSqsTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\AmqpTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\DoctrineTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\RedisTransportFactory;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransport;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class TransportFactory
{
    public function __construct(
        private readonly MessageBusContainer $message_bus_locator,
        private readonly ClockInterface $clock,
        private readonly RedisTransportFactory $redis_transport_factory,
        private readonly DoctrineTransportFactory $doctrine_transport_factory,
        private readonly AmqpTransportFactory $amqp_transport_factory,
        private readonly AmazonSqsTransportFactory $amazon_sqs_transport_factory,
    ) {
    }

    public function make(array $config): TransportInterface
    {
        \assert(Type::isClassStringOf(TransportInterface::class, $config['class'] ?? null));

        return match ($config['class']) {
            RedisTransport::class => $this->redis_transport_factory->make(
                $config['connection'] ?? RedisManager::DEFAULT,
                $config['options'] ?? [],
            ),
            DoctrineTransport::class => $this->doctrine_transport_factory->make(
                $config['connection'] ?? ConnectionFactory::DEFAULT,
                $config['options'] ?? [],
            ),
            AmqpTransport::class => $this->amqp_transport_factory->make($config['options'] ?? []),
            AmazonSqsTransport::class => $this->amazon_sqs_transport_factory->make($config['options'] ?? []),
            SyncTransport::class => new SyncTransport($this->message_bus_locator->get($config['bus'] ?? MessageBus::DEFAULT)),
            InMemoryTransport::class => new InMemoryTransport(null, $this->clock),
            default => throw new \UnexpectedValueException("Unsupported transport class: " . $config['class']),
        };
    }
}
