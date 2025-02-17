<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash\Hash;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash\HashAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash\Hmac;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\KeyChain;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Random\Random;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\MessageSignature;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Symmetric;

/**
 * A facade pattern implementation around our cryptographic utilities, which are
 * almost entirely based on the Sodium extension. Using the facade instead of the
 * concrete implementations allows us a bit more future flexibility in changing
 * the underlying implementation (or adding configuration), should that ever be
 * necessary.
 *
 * Without the facade, consuming code would need to bring in several related classes
 * that interact. This way, they only need to know about the Natrium class, and can
 * use it to access the various cryptographic functions.
 *
 * Note: we don't directly expose the asymmetric seal/unseal methods, as we should
 * always prefer authenticated encryption to anonymous encryption, unless there is
 * a specific reason to use the latter. Those methods can be accessed through the
 * public $asymmetric property, if needed.
 */
#[Contract]
readonly class Natrium
{
    public Symmetric $symmetric;
    public Asymmetric $asymmetric;
    public Random $random;
    public KeyChain $keys;

    public function __construct(SharedKey $app_key)
    {
        $this->keys = new KeyChain($app_key);
        $this->symmetric = new Symmetric();
        $this->asymmetric = new Asymmetric();
        $this->random = new Random();
    }

    public function hash(\Stringable|BinaryString|string $plaintext): Hash
    {
        return Hash::string(self::cast($plaintext), HashAlgorithm::BLAKE2B);
    }

    public function hmac(\Stringable|BinaryString|string $plaintext, string|null $context = null): Hmac
    {
        return Hmac::string(self::cast($plaintext), $this->keys->shared($context), HashAlgorithm::BLAKE2B);
    }

    public function encrypt(
        \Stringable|BinaryString|string $plaintext,
        string|null $context = null,
        \Stringable|BinaryString|string $additional_data = '',
    ): Ciphertext {
        return $this->symmetric->encrypt(
            $this->keys->shared($context),
            self::cast($plaintext),
            self::cast($additional_data),
        );
    }

    public function decrypt(
        Ciphertext $ciphertext,
        string|null $context = null,
        \Stringable|BinaryString|string $additional_data = '',
    ): string|null {
        return $this->symmetric->decrypt(
            $this->keys->shared($context),
            $ciphertext,
            self::cast($additional_data),
        );
    }

    public function sign(
        \Stringable|BinaryString|string $plaintext,
        string|null $context = null,
    ): MessageSignature {
        return $this->symmetric->sign(
            $this->keys->shared($context),
            self::cast($plaintext),
        );
    }

    public function verify(
        \Stringable|BinaryString|string $plaintext,
        MessageSignature $signature,
        string|null $context = null,
    ): bool {
        return $this->symmetric->verify(
            $this->keys->shared($context),
            $signature,
            self::cast($plaintext),
        );
    }

    public function encryptWithPublicKey(
        EncryptionPublicKey $public_key,
        \Stringable|BinaryString|string $plaintext,
        \Stringable|BinaryString|string $additional_data = '',
    ): Ciphertext {
        return $this->asymmetric->encrypt(
            $this->keys->encryption(),
            $public_key,
            self::cast($plaintext),
            self::cast($additional_data),
        );
    }

    /**
     * Asymmetric decryption using the public key of the sender to authenticate
     * that the message was sent by them, and the secret key of the recipient to
     * decrypt the message.
     */
    public function decryptWithSecretKey(
        EncryptionPublicKey $public_key,
        Ciphertext $ciphertext,
        \Stringable|BinaryString|string $additional_data = '',
    ): string|null {
        return $this->asymmetric->decrypt(
            $this->keys->encryption(),
            $public_key,
            $ciphertext,
            self::cast($additional_data),
        );
    }

    /**
     * Create a digital signature for a message using the secret key, so that anyone
     * with the public key can verify the authenticity of the message.
     */
    public function signWithSecretKey(\Stringable|BinaryString|string $plaintext): MessageSignature
    {
        return $this->asymmetric->sign(
            $this->keys->signature(),
            self::cast($plaintext),
        );
    }

    /**
     * Verify the authenticity of a plaintext message with a detached message
     * signature produced with the sender's secret key, using their known public key.
     */
    public function verifyWithPublicKey(
        SignaturePublicKey $sender_public_key,
        MessageSignature $signature,
        \Stringable|BinaryString|string $plaintext,
    ): bool {
        return $this->asymmetric->verify(
            $sender_public_key,
            $signature,
            self::cast($plaintext),
        );
    }

    private static function cast(\Stringable|BinaryString|string $string): string
    {
        return $string instanceof BinaryString ? $string->bytes() : (string)$string;
    }
}
