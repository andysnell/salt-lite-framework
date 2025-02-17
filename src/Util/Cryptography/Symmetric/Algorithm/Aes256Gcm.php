<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\CryptoLogicException;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\EncryptionAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Symmetric;

/**
 * Symmetric Encryption: AES-256-GCM AEAD
 *
 * AES-256-GCM is a AEAD construction based on the AES-256 block cipher in
 * Galois/Counter Mode. It is a widely supported and secure symmetric encryption,
 * and when hardware-accelerated, can be very fast, though not as fast as
 * AEGIS-256. It's included in the SaltLite framework for compatibility with
 * external applications that use this algorithm.
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class Aes256Gcm implements EncryptionAlgorithm
{
    public const int KEY_BYTES = \SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): Ciphertext {
        \sodium_crypto_aead_aes256gcm_is_available() || throw new CryptoLogicException(
            'AES-256-GCM is not available on this system',
        );

        $nonce = \random_bytes(\SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES);
        return new Ciphertext($nonce . \sodium_crypto_aead_aes256gcm_encrypt(
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
        \sodium_crypto_aead_aes256gcm_is_available() || throw new CryptoLogicException(
            'AES-256-GCM is not available on this system',
        );

        if ($ciphertext->length() <= \SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_aead_aes256gcm_decrypt(
            \substr($ciphertext->bytes(), \SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES),
            $additional_data,
            \substr($ciphertext->bytes(), 0, \SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES),
            $key->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }
}
