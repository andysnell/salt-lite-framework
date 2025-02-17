<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception;

class CryptoLogicException extends \LogicException implements CryptoException
{
    public static function unreachable(): self
    {
        return new self('A code path was executed that would not normally be possible under normal operation.');
    }
}
