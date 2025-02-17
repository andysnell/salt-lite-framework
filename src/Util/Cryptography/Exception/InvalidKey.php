<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception;

class InvalidKey extends CryptoRuntimeException
{
    public static function length(int $expected): self
    {
        return new self(\sprintf("Key Must Be Exactly %d Bytes", $expected));
    }

    public static function decoding(): self
    {
        return new self("Unable to Decode Key into Binary String of Expected Length");
    }
}
