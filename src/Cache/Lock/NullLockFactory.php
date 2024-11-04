<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;

final class NullLockFactory implements LockFactory
{
    #[\Override]
    public function make(\Stringable|string|NamedKey $key, Ttl $ttl = new Ttl(300), bool $auto_release = true): NullLock
    {
        return new NullLock($ttl);
    }
}
