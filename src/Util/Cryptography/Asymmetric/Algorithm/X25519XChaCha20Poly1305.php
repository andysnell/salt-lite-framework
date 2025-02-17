<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\KeyExchange;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\XChaCha20Poly1305;

/**
 * Diffie-Hellman key exchange over Curve25519 + XChaCha20 + Poly130 (IETF) AEAD
 *
 * @see XChaCha20Poly1305 for more information on the encryption algorithm details
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Asymmetric::class)]
final readonly class X25519XChaCha20Poly1305 implements EncryptionAlgorithm
{
    use HasCommonAnonymousEncryptionBehavior;

    public const int KEY_PAIR_BYTES = \SODIUM_CRYPTO_KX_KEYPAIRBYTES; // 64 bytes
    public const int PUBLIC_KEY_BYTES = \SODIUM_CRYPTO_KX_PUBLICKEYBYTES; // 32 bytes
    public const int SECRET_KEY_BYTES = \SODIUM_CRYPTO_KX_SECRETKEYBYTES; // 32 bytes

    public static function encrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): Ciphertext {
        return XChaCha20Poly1305::encrypt(
            KeyExchange::encryption($key_pair, $public_key),
            $plaintext,
            $additional_data,
        );
    }

    public static function decrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        return XChaCha20Poly1305::decrypt(
            KeyExchange::decryption($key_pair, $public_key),
            $ciphertext,
            $additional_data,
        );
    }
}
