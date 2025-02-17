<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\Key;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringFromRandomBytes;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringProhibitsSerialization;

/**
 * 256-bit symmetric key for use with XChaCha20 or AEGIS-256 ciphers
 *
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class SharedKey extends FixedLengthBinaryString implements Key
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringFromRandomBytes;

    public const int LENGTH = \SODIUM_CRYPTO_STREAM_XCHACHA20_KEYBYTES;
}
