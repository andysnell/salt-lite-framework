<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;

use PhoneBurner\SaltLite\App\Environment;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\MessageBus\Config\TransportConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory;
use Symfony\Component\Messenger\Bridge\Redis\Transport\Connection as RedisTransportConnection;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport;

use function PhoneBurner\SaltLite\Framework\ghost;

class RedisTransportFactory implements TransportFactory
{
    public function __construct(
        private readonly RedisManager $redis_manager,
        private readonly Environment $environment,
    ) {
    }

    /**
     * @see RedisTransportConnection::DEFAULT_OPTIONS for available options
     */
    public function make(TransportConfigStruct $config): RedisTransport
    {
        \assert($config->class === RedisTransport::class);

        $options = $config->options;
        $options['consumer'] ??= $this->environment->hostname();
        return ghost(fn(RedisTransport $ghost): null => $ghost->__construct(
            /** @phpstan-ignore new.internalClass */
            new RedisTransportConnection($options, $this->redis_manager->connect($config->connection)),
        ));
    }
}
