<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Handler;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionId;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;

#[Internal]
final class RedisSessionHandler extends SessionHandler
{
    public function __construct(
        private readonly \Redis $redis,
        private readonly Ttl $ttl,
    ) {
    }

    public function read(SessionId|string $id): string
    {
        return (string)$this->redis->get(self::key($id));
    }

    public function write(SessionId|string $id, string $data): bool
    {
        return $this->redis->setex(self::key($id), $this->ttl->inSeconds(), $data);
    }

    public function destroy(SessionId|string $id): bool
    {
        return (bool)$this->redis->del(self::key($id));
    }

    /**
     * Note: we cannot use PhoneBurner\SaltLite\Framework\Cache\CacheKey::make()
     * like we normally would for creating a normalized cache key because our session
     * key has invalid characters for a PSR-6, which get converted to underscores,
     * reducing the variance of the session id.
     */
    private static function key(SessionId|string $id): string
    {
        return 'session.' . $id;
    }
}
