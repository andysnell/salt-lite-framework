<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringImportExportBehavior;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringProhibitsSerialization;

/**
 * Holds a secret key and the corresponding public key for encryption and
 * decryption of messages using X25519.
 *
 * Note that the ED25519 Key Pair used for signing and verifying messages is
 * not the same as the X25519 Key Pair used for encryption and decryption, though
 * the former can be converted to the latter.
 */
final readonly class EncryptionKeyPair implements KeyPair
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringImportExportBehavior;

    final public const int LENGTH = \SODIUM_CRYPTO_KX_KEYPAIRBYTES;

    final public const int SEED_LENGTH = \SODIUM_CRYPTO_KX_SEEDBYTES;

    public EncryptionSecretKey $secret;
    public EncryptionPublicKey $public;

    public function __construct(#[\SensitiveParameter] BinaryString|string $bytes)
    {
        $bytes = $bytes instanceof BinaryString ? $bytes->bytes() : $bytes;
        if (\strlen($bytes) !== self::LENGTH) {
            throw InvalidKeyPair::length(self::LENGTH);
        }

        $this->secret = new EncryptionSecretKey(\sodium_crypto_kx_secretkey($bytes));
        $this->public = new EncryptionPublicKey(\sodium_crypto_kx_publickey($bytes));
    }

    final public static function generate(): static
    {
        return new self(\sodium_crypto_kx_keypair());
    }

    /**
     * The underlying "keypair from seed" function takes the input seed, hashes it
     * with BLAKE2b, and then uses the first 32 bytes as the secret key, and derives
     * the public key from that. Note that this is does not have the same output
     * as the older \sodium_crypto_box_seed_keypair function, which uses SHA-512/256
     * as the hash function.
     */
    public static function fromSeed(#[\SensitiveParameter] string $seed): static
    {
        if (\strlen($seed) !== \SODIUM_CRYPTO_KX_SEEDBYTES) {
            throw new \UnexpectedValueException('Key Pair seed must be ' . \SODIUM_CRYPTO_KX_SEEDBYTES . ' bytes');
        }

        return new self(\sodium_crypto_kx_seed_keypair($seed));
    }

    public static function fromSecretKey(SignatureSecretKey|EncryptionSecretKey $secret_key): self
    {
        if ($secret_key instanceof SignatureSecretKey) {
            $secret_key = new EncryptionSecretKey(\sodium_crypto_sign_ed25519_sk_to_curve25519($secret_key->bytes()));
        }

        return new self(\sodium_crypto_box_keypair_from_secretkey_and_publickey(
            $secret_key->bytes(),
            \sodium_crypto_box_publickey_from_secretkey($secret_key->bytes()),
        ));
    }

    public function secret(): EncryptionSecretKey
    {
        return $this->secret;
    }

    public function public(): EncryptionPublicKey
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
