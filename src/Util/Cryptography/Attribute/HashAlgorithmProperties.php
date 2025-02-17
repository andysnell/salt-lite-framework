<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
final class HashAlgorithmProperties
{
    public function __construct(
        public int $digest_bytes,
        public bool $cryptographic = false,
        public bool $broken = false,
    ) {
    }
}
