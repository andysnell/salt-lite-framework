<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class SignaturePublicKey extends FixedLengthBinaryString implements PublicKey
{
    public const int LENGTH = \SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES; // 256-bit string
}
