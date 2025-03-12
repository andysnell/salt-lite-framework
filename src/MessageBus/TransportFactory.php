<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Framework\MessageBus\Config\TransportConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\MessageBusContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\AmazonSqsTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\AmqpTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\DoctrineTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\RedisTransportFactory;
use PhoneBurner\SaltLite\MessageBus\MessageBus;
use PhoneBurner\SaltLite\Type\Type;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransport;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport;
use Symfony\Component\Messenger\MessageBusInterface;
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

    public function make(TransportConfigStruct $config): TransportInterface
    {
        \assert(Type::isClassStringOf(TransportInterface::class, $config->class));

        return match ($config->class) {
            RedisTransport::class => $this->redis_transport_factory->make($config->connection, $config->options),
            DoctrineTransport::class => $this->doctrine_transport_factory->make($config->connection, $config->options),
            AmqpTransport::class => $this->amqp_transport_factory->make($config->connection, $config->options),
            AmazonSqsTransport::class => $this->amazon_sqs_transport_factory->make($config->connection, $config->options),
            SyncTransport::class => new SyncTransport(Type::of(
                MessageBusInterface::class,
                $this->message_bus_locator->get($config->options['bus'] ?? MessageBus::DEFAULT),
            )),
            InMemoryTransport::class => new InMemoryTransport(null, $this->clock),
            default => throw new \UnexpectedValueException("Unsupported transport class: " . $config->class),
        };
    }
}
