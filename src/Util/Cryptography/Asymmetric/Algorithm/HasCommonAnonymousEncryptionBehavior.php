<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;

/**
 * @phpstan-require-implements EncryptionAlgorithm
 */
trait HasCommonAnonymousEncryptionBehavior
{
    public static function seal(
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): Ciphertext {
        // Generate a random, ephemeral keypair to use as the secret component
        $key_pair = EncryptionKeyPair::generate();

        // Prepend the public key to the encrypted ciphertext
        return new Ciphertext($key_pair->public->bytes() . self::encrypt(
            $key_pair,
            $public_key,
            $plaintext,
            $additional_data,
        )->bytes());
    }

    public static function unseal(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        if ($ciphertext->length() < \SODIUM_CRYPTO_KX_PUBLICKEYBYTES) {
            return null;
        }

        return self::decrypt(
            $key_pair,
            new EncryptionPublicKey(\substr($ciphertext->bytes(), 0, \SODIUM_CRYPTO_KX_PUBLICKEYBYTES)),
            new Ciphertext(\substr($ciphertext->bytes(), \SODIUM_CRYPTO_KX_PUBLICKEYBYTES)),
            $additional_data,
        );
    }
}
