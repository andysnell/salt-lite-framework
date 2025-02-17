<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\EncryptionAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Symmetric;

/**
 * Symmetric Encryption: XChaCha20-Poly1305 IETF AEAD
 *
 * The extended-nonce, IETF variant of the ChaCha20 cipher paired with a Poly1305
 * message authentication tag was the preferred symmetric encryption algorithm
 * prior to the introduction of the AEGIS-256 construction, and concerns over
 * key-commitment and partitioning attacks. It is still considered secure and is
 * implemented here for interoperability with external applications that use this
 * algorithm. However, where possible, the AEGIS-256 or XChaCha20-Blake2b
 * construction, which uses the same cipher but with split encryption/authentication
 * keys and Blake2b MAC instead of Poly1305, should be used instead.
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class XChaCha20Poly1305 implements EncryptionAlgorithm
{
    public const int KEY_BYTES = \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): Ciphertext {
        $nonce = \random_bytes(\SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        return new Ciphertext($nonce . \sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $additional_data,
            $nonce,
            $key->bytes(),
        ));
    }

    public static function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        if ($ciphertext->length() <= \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            \substr($ciphertext->bytes(), \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES),
            $additional_data,
            \substr($ciphertext->bytes(), 0, \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES),
            $key->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }
}
