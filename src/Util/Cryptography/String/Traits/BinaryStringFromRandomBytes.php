<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;

/**
 * @phpstan-require-extends FixedLengthBinaryString
 */
trait BinaryStringFromRandomBytes
{
    public static function generate(): self
    {
        return new self(\random_bytes(self::LENGTH));
    }
}
