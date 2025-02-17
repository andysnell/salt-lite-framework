<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringProhibitsSerialization;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class SignatureSecretKey extends FixedLengthBinaryString implements SecretKey
{
    use BinaryStringProhibitsSerialization;

    public const int LENGTH = \SODIUM_CRYPTO_SIGN_SECRETKEYBYTES; // 512-bit string
}
