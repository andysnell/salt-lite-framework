<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface LockFactory
{
    public function make(NamedKey|\Stringable|string $key, Ttl $ttl = new Ttl(300), bool $auto_release = true): Lock;
}
