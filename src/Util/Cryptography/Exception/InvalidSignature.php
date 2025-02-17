<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception;

class InvalidSignature extends CryptoRuntimeException
{
    public static function length(int $expected): self
    {
        return new self(\sprintf("Message Signature Must Be Exactly %d Bytes", $expected));
    }
}
