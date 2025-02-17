<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception;

class InvalidKeyPair extends CryptoRuntimeException
{
    public static function length(int $expected): self
    {
        return new self(\sprintf("Key Pair Must Be Exactly %d Bytes", $expected));
    }
}
