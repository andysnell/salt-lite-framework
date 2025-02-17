<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\KeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\SignatureSecretKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidStringLength;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash\Hash;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash\HashAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\Key;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringFromRandomBytes;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringProhibitsSerialization;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NonEmpty;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class PasetoKey extends FixedLengthBinaryString implements Key, KeyPair
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringFromRandomBytes;

    public const int LENGTH = \SODIUM_CRYPTO_SIGN_SEEDBYTES; // 256-bit string

    /**
     * Generate a new AppKey instance from a random 256-bit value.
     */
    public static function generate(): static
    {
        return new self(\random_bytes(self::LENGTH));
    }

    /**
     * Make a key from an existing string input as a seed. If the input is exactly
     * the correct length, it is assumed to be a raw key, and is used as-is. If it
     * is longer, we use the BLAKE2b hash function to derive a key of the correct
     * length from the input.
     */
    public static function fromSeed(#[\SensitiveParameter] string $seed): static
    {
        $key_length = \strlen($seed);
        return match (true) {
            $key_length === self::LENGTH => new self(NonEmpty::string($seed)),
            $key_length > self::LENGTH => new self(\sodium_crypto_generichash($seed, '', self::LENGTH)),
            default => throw new InvalidStringLength(self::LENGTH),
        };
    }

    public function id(): string
    {
        return 'sha256:' . Hash::string($this->bytes(), HashAlgorithm::SHA256);
    }

    /**
     * Shared Key for Authenticated Symmetric Key Encryption
     */
    public function shared(): SharedKey
    {
        return new SharedKey($this->bytes());
    }

    /**
     * Secret Key for Asymmetric Key Signature Authentication
     */
    public function secret(): SignatureSecretKey
    {
        return SignatureKeyPair::fromSeed($this->bytes())->secret();
    }

    /**
     * Public Key for Asymmetric Key Signature Authentication
     */
    public function public(): SignaturePublicKey
    {
        return SignatureKeyPair::fromSeed($this->bytes())->public();
    }
}
