<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception;

class InvalidStringLength extends CryptoRuntimeException
{
    public function __construct(int $expected)
    {
        parent::__construct(\sprintf('String Must Be Exactly %d Bytes', $expected));
    }
}
