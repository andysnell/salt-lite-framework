<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidKeySeed;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringImportExportBehavior;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringProhibitsSerialization;

/**
 * Holds a secret key and the corresponding public key for signing and verifying
 * messages using ED25519 (EdDSA).
 */
final readonly class SignatureKeyPair implements KeyPair
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringImportExportBehavior;

    public const int LENGTH = \SODIUM_CRYPTO_SIGN_KEYPAIRBYTES;

    public const int SEED_LENGTH = \SODIUM_CRYPTO_SIGN_SEEDBYTES;

    public SignatureSecretKey $secret;
    public SignaturePublicKey $public;

    public function __construct(#[\SensitiveParameter] BinaryString|string $bytes)
    {
        $bytes = $bytes instanceof BinaryString ? $bytes->bytes() : $bytes;
        if (\strlen($bytes) !== self::LENGTH) {
            throw InvalidKeyPair::length(self::LENGTH);
        }

        $this->secret = new SignatureSecretKey(\sodium_crypto_sign_secretkey($bytes));
        $this->public = new SignaturePublicKey(\sodium_crypto_sign_publickey($bytes));
    }

    public static function generate(): static
    {
        return new self(\sodium_crypto_sign_keypair());
    }

    /**
     * Important: Unlike the EncryptionKeyPair, the seed for the SignatureKeyPair
     * is used as first 256-bits of the 512-bit secret key. Therefore, using a
     * key derivation function to create the seed from a master key would be a
     * good idea.
     */
    public static function fromSeed(#[\SensitiveParameter] string $seed): static
    {
        if (\strlen($seed) !== self::SEED_LENGTH) {
            throw InvalidKeySeed::length(self::SEED_LENGTH);
        }

        return new self(\sodium_crypto_sign_seed_keypair($seed));
    }

    public static function fromSecretKey(SignatureSecretKey $secret_key): self
    {
        return new self(\sodium_crypto_sign_keypair_from_secretkey_and_publickey(
            $secret_key->bytes(),
            \sodium_crypto_sign_publickey_from_secretkey($secret_key->bytes()),
        ));
    }

    public function secret(): SignatureSecretKey
    {
        return $this->secret;
    }

    public function public(): SignaturePublicKey
    {
        return $this->public;
    }

    public function bytes(): string
    {
        return $this->secret->bytes() . $this->public->bytes();
    }

    public function length(): int
    {
        return self::LENGTH;
    }
}
