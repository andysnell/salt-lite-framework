<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringProhibitsSerialization;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class EncryptionSecretKey extends FixedLengthBinaryString implements SecretKey
{
    use BinaryStringProhibitsSerialization;

    final public const int LENGTH = \SODIUM_CRYPTO_KX_SECRETKEYBYTES; // 256-bit string
}
