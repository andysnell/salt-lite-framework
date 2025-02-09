<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Redis;

use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Exception\InvalidConfiguration;
use PhoneBurner\SaltLite\Framework\Database\Redis\Exception\RedisConnectionFailure;
use Redis;
use RedisException;

class CachingRedisManager implements RedisManager
{
    private array $connections = [];

    public function __construct(
        private readonly Configuration $config,
    ) {
    }

    #[\Override]
    public function connect(string $connection = self::DEFAULT): Redis
    {
        return $this->connections[$connection] ??= $this->doConnect($connection);
    }

    private function doConnect(string $connection): Redis
    {
        $config = $this->config->get("database.redis.connections.$connection") ?? [];

        try {
            $client = new Redis();
            $client->pconnect(
                $config['host'] ?? throw new InvalidConfiguration('Redis Config Invalid: Host'),
                $config['port'] ?? throw new InvalidConfiguration('Redis Config Invalid: Port'),
                $this->config->get('redis.timeout') ?? 0.0,
                $connection,
            ) ?: throw new RedisConnectionFailure('Unable to Connect');
        } catch (RedisException $e) {
            throw new RedisConnectionFailure('Unable to Connect: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $client;
    }
}
