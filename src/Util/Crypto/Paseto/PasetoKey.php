<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NonEmpty;

final readonly class PasetoKey
{
    public const int KEY_LENGTH_BYTES = 32;

    /** @param non-empty-string $key */
    public function __construct(#[\SensitiveParameter] private string $key)
    {
        if (\strlen($this->key) !== self::KEY_LENGTH_BYTES) {
            throw new \InvalidArgumentException('Invalid Key Length');
        }
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
        return 'blake2b:' . \bin2hex(\sodium_crypto_generichash($this->key));
    }

    /**
     * Shared Key for Authenticated Symmetric Key Encryption
     *
     * @return non-empty-string
     */
    public function shared(): string
    {
        return $this->key;
    }

    /**
     * Secret Key for Asymmetric Key Signature Authentication
     *
     * @return non-empty-string
     */
    public function secret(): string
    {
        return \sodium_crypto_sign_secretkey(\sodium_crypto_sign_seed_keypair($this->key));
    }

    /**
     * Public Key for Asymmetric Key Signature Authentication
     *
     * @return non-empty-string
     */
    public function public(): string
    {
        return \sodium_crypto_sign_publickey(\sodium_crypto_sign_seed_keypair($this->key));
    }
}
