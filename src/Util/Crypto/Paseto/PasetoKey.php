<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\Hash;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\HashAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Key;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Util;
use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NonEmpty;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class PasetoKey implements Key
{
    public const int KEY_LENGTH_BYTES = \SODIUM_CRYPTO_SIGN_SEEDBYTES;

    /**
     * @param non-empty-string $value
     */
    public function __construct(#[\SensitiveParameter] private string $value)
    {
        if (\strlen($this->value) !== self::KEY_LENGTH_BYTES) {
            throw new \InvalidArgumentException('Invalid Key Length');
        }
    }

    /**
     * Wrap another Key instance in a PasetoKey object without additional derivation.
     */
    public static function wrap(#[\SensitiveParameter] Key $key): self
    {
        return new self($key->bytes());
    }

    /**
     * Zero out the key in memory when the object is destroyed.
     */
    public function __destruct()
    {
        Util::memzero($this->value);
    }

    public static function length(): int
    {
        return self::KEY_LENGTH_BYTES;
    }

    /**
     * Make a key from an existing string input.
     */
    public static function make(#[\SensitiveParameter] string $key): self
    {
        $key_length = \strlen($key);
        return match (true) {
            $key_length === self::KEY_LENGTH_BYTES => new self(NonEmpty::string($key)),
            $key_length > self::KEY_LENGTH_BYTES => new self(\sodium_crypto_generichash($key, '', self::KEY_LENGTH_BYTES)),
            default => throw new \InvalidArgumentException('Invalid Key Length'),
        };
    }

    public function id(): string
    {
        return 'sha256:' . Hash::string($this->value, HashAlgorithm::SHA256);
    }

    public function bytes(): string
    {
        return $this->value;
    }

    public function encoded(Encoding $encoding = Encoding::Base64): string
    {
        return match ($encoding) {
            Encoding::None => $this->value,
            Encoding::Hex => Util::encode(Encoding::Hex, $this->value),
            default => 'base64:' . Util::encode($encoding, $this->value)
        };
    }

    /**
     * Shared Key for Authenticated Symmetric Key Encryption
     */
    public function shared(): SharedKey
    {
        return new SharedKey($this->value);
    }

    /**
     * Secret Key for Asymmetric Key Signature Authentication
     *
     * @return non-empty-string
     */
    public function secret(): string
    {
        return \sodium_crypto_sign_secretkey(\sodium_crypto_sign_seed_keypair($this->value));
    }

    /**
     * Public Key for Asymmetric Key Signature Authentication
     *
     * @return non-empty-string
     */
    public function public(): string
    {
        return \sodium_crypto_sign_publickey(\sodium_crypto_sign_seed_keypair($this->value));
    }
}
