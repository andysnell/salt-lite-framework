<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie;

readonly class QueuedCookie
{
    public function __construct(
        public Cookie $cookie,
        public bool $encrypt = false,
    ) {
    }
}
