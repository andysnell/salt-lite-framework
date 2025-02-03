<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Key;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Algorithm\XChaCha20Blake2b;

/**
 * Symmetric AEAD encryption using XChaCha20 with a BLAKE2b-MAC for message
 * authentication. This is a modern approach that addresses several shortcomings
 * around both the `\sodium_crypto_secretbox()` and \sodium_crypto_aead_*() APIs.
 * Specifically, the algorithm below mitigates:
 *  - Chosen-Ciphertext Attacks
 *  - Key-Commitment aka "Invisible Salamander" attacks
 *  - Timing Attacks on the MAC
 *
 * @see https://github.com/paragonie/halite/blob/master/src/Symmetric/Crypto.php
 * for the reference implementation this is based on.
 */
#[Contract]
class Symmetric
{
    public const int MIN_CIPHERTEXT_BYTES = XChaCha20Blake2b::MIN_CIPHERTEXT_BYTES;

    public function encrypt(
        #[\SensitiveParameter] Key $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
        Encoding $encoding = Encoding::Base64,
    ): string {
        return XChaCha20Blake2b::encrypt($key, $plaintext, $additional_data, $encoding);
    }

    public function decrypt(
        #[\SensitiveParameter] Key $key,
        #[\SensitiveParameter] string $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
        Encoding $encoding = Encoding::Base64,
    ): string {
        return XChaCha20Blake2b::decrypt($key, $ciphertext, $additional_data, $encoding);
    }
}
