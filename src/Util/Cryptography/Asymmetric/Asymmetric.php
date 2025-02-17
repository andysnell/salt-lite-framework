<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidSignature;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\MessageSignature;

class Asymmetric
{
    public function encrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] \Stringable|string $plaintext,
        #[\SensitiveParameter] \Stringable|string $additional_data = '',
        Algorithm $algorithm = Algorithm::X25519XChaCha20Blake2b,
    ): Ciphertext {
        return $algorithm->implementation()::encrypt(
            $key_pair,
            $public_key,
            $plaintext instanceof BinaryString ? $plaintext->bytes() : (string)$plaintext,
            (string)$additional_data,
        );
    }

    public function decrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] \Stringable|string $additional_data = '',
        Algorithm $algorithm = Algorithm::X25519XChaCha20Blake2b,
    ): string|null {
        return $algorithm->implementation()::decrypt(
            $key_pair,
            $public_key,
            $ciphertext,
            (string)$additional_data,
        );
    }

    /**
     * Anonymous Asymmetric Encryption
     *
     * Encrypt a string with the recipient's public key, so that only the recipient
     * can decrypt it with their private key.
     */
    public function seal(
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] \Stringable|string $plaintext,
        Algorithm $algorithm = Algorithm::X25519XChaCha20Blake2b,
    ): Ciphertext {
        return $algorithm->implementation()::seal($public_key, $plaintext instanceof BinaryString ? $plaintext->bytes() : (string)$plaintext);
    }

    /**
     * Anonymous Asymmetric Encryption
     * Decrypt an encrypted string using the secret key.
     */
    public function unseal(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        Algorithm $algorithm = Algorithm::X25519XChaCha20Blake2b,
    ): string|null {
        return $algorithm->implementation()::unseal($key_pair, $ciphertext);
    }

    /**
     * Create a detached Ed25519 digital signature for a message.
     */
    public function sign(
        #[\SensitiveParameter] SignatureKeyPair $key_pair,
        #[\SensitiveParameter] \Stringable|string $plaintext,
    ): MessageSignature {
        return new MessageSignature(\sodium_crypto_sign_detached(
            $plaintext instanceof BinaryString ? $plaintext->bytes() : (string)$plaintext,
            $key_pair->secret->bytes(),
        ));
    }

    /**
     * Verify a detached Ed25519 digital signature for a message.
     */
    public function verify(
        #[\SensitiveParameter] SignaturePublicKey $public_key,
        #[\SensitiveParameter] MessageSignature $signature,
        #[\SensitiveParameter] \Stringable|string $plaintext,
    ): bool {
        return \sodium_crypto_sign_verify_detached(
            $signature->bytes() ?: throw new InvalidSignature('Signature Cannot Be Empty'),
            $plaintext instanceof BinaryString ? $plaintext->bytes() : (string)$plaintext,
            $public_key->bytes(),
        );
    }
}
