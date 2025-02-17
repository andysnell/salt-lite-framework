<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\String;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;

/**
 * A 512-bit digital signature created with either the Symmetric::sign() or
 * Asymmetric::sign() methods.
 */
final class MessageSignature extends FixedLengthBinaryString
{
    public const int LENGTH = \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX; // 512-bit digest

    public function first(int $n): VariableLengthBinaryString
    {
        return new VariableLengthBinaryString(\substr($this->bytes(), 0, $n));
    }
}
