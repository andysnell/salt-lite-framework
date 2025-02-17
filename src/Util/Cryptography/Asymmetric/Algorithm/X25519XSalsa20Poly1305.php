<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\CryptoLogicException;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;

/**
 * Implements the X25519-XSalsa20-Poly1305 encryption algorithm using the default
 * libsodium implementations, e.g. \sodium_crypto_box() and \sodium_crypto_box_seal().
 *
 * @see XSalsa20Poly1305 for more information on the encryption algorithm details
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Asymmetric::class)]
final readonly class X25519XSalsa20Poly1305 implements EncryptionAlgorithm
{
    public const int KEY_PAIR_BYTES = \SODIUM_CRYPTO_BOX_KEYPAIRBYTES; // 64 bytes
    public const int PUBLIC_KEY_BYTES = \SODIUM_CRYPTO_BOX_PUBLICKEYBYTES; // 32 bytes
    public const int SECRET_KEY_BYTES = \SODIUM_CRYPTO_BOX_SECRETKEYBYTES; // 32 bytes

    public static function encrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): Ciphertext {
        self::assertAssociatedDataLength($additional_data);

        // The \sodium_crypto_box() function consumes a single key pair
        // value made up of the recipient's public key and the sender's secret key.
        $box_key_pair = \sodium_crypto_box_keypair_from_secretkey_and_publickey(
            \sodium_crypto_box_secretkey($key_pair->bytes()),
            $public_key->bytes(),
        );

        $nonce = \random_bytes(\SODIUM_CRYPTO_BOX_NONCEBYTES);
        $ciphertext = $nonce . \sodium_crypto_box($plaintext, $nonce, $box_key_pair);

        \sodium_memzero($box_key_pair);

        return new Ciphertext($ciphertext);
    }

    public static function decrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::assertAssociatedDataLength($additional_data);

        // The \sodium_crypto_box_open() function consumes a single key pair
        // value made up of the recipient's secret key and the sender's public key.
        $box_key_pair = \sodium_crypto_box_keypair_from_secretkey_and_publickey(
            \sodium_crypto_box_secretkey($key_pair->bytes()),
            $public_key->bytes(),
        );

        $plaintext = \sodium_crypto_box_open(
            \substr($ciphertext->bytes(), \SODIUM_CRYPTO_BOX_NONCEBYTES),
            \substr($ciphertext->bytes(), 0, \SODIUM_CRYPTO_BOX_NONCEBYTES),
            $box_key_pair,
        );

        \sodium_memzero($box_key_pair);

        return $plaintext !== false ? $plaintext : null;
    }

    public static function seal(
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): Ciphertext {
        self::assertAssociatedDataLength($additional_data);
        return new Ciphertext(\sodium_crypto_box_seal($plaintext, $public_key->bytes()));
    }

    public static function unseal(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::assertAssociatedDataLength($additional_data);
        if ($ciphertext->length() < \SODIUM_CRYPTO_BOX_NONCEBYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_box_seal_open($ciphertext->bytes(), $key_pair->bytes());

        return $plaintext !== false ? $plaintext : null;
    }

    private static function assertAssociatedDataLength(string $additional_data): void
    {
        $additional_data === '' || throw new CryptoLogicException(
            'X25519-XSalsa20-Poly1305 is not an AEAD Construction',
        );
    }
}
