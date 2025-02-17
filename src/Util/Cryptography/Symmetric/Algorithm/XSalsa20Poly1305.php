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
 * Symmetric Encryption: XSalsa20-Poly1305
 *
 * The XSalsa20-Poly1305 authenticated encryption algorithm using the sodium
 * \sodium_crypto_secretbox() function. We're implementing this algorithm for
 * backwards compatibility and interoperability with external applications that
 * use this algorithm; however, we always want to use one of the AEAD constructions
 * like AEGIS-256 or XChaCha20-Blake2b, even if we don't use the additional data
 * field.
 *
 * Note: This is not a AEAD construction, and passing additional data will result
 * in an exception being thrown. Since this is the sole implementation of a non-AEAD
 * symmetric encryption algorithm, and is only intended for fallback usage in
 * specific cases, there is no separate interface for it.
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class XSalsa20Poly1305 implements EncryptionAlgorithm
{
    public const int KEY_BYTES = \SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): Ciphertext {
        self::assertAssociatedDataLength($additional_data);

        $nonce = \random_bytes(\SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return new Ciphertext($nonce . \sodium_crypto_secretbox(
            $plaintext,
            $nonce,
            $key->bytes(),
        ));
    }

    public static function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::assertAssociatedDataLength($additional_data);
        if ($ciphertext->length() <= \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_secretbox_open(
            \substr($ciphertext->bytes(), \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            \substr($ciphertext->bytes(), 0, \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            $key->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }

    private static function assertAssociatedDataLength(string $additional_data): void
    {
        $additional_data === '' || throw new CryptoLogicException(
            'XSalsa20-Poly1305 is not an AEAD Construction',
        );
    }
}
