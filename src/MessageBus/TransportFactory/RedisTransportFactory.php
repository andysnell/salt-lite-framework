<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use Symfony\Component\Messenger\Bridge\Redis\Transport\Connection as RedisTransportConnection;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport;

use function PhoneBurner\SaltLite\Framework\ghost;

class RedisTransportFactory
{
    public function __construct(
        private readonly RedisManager $redis_manager,
        private readonly Environment $environment,
    ) {
    }

    /**
     * @see RedisTransportConnection::DEFAULT_OPTIONS for available options
     */
    public function make(string $connection, array $options): RedisTransport
    {
        $options['consumer'] ??= $this->environment->hostname();
        return ghost(fn(RedisTransport $ghost): null => $ghost->__construct(
            new RedisTransportConnection($options, $this->redis_manager->connect($connection)),
        ));
    }
}
