<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto;

final readonly class PasetoKey
{
    public const int MINIMUM_LENGTH = 32;

    private function __construct(private string $key)
    {
        if (\strlen($this->key) < self::MINIMUM_LENGTH) {
            throw new \InvalidArgumentException('Invalid Key Length');
        }
    }

    public static function make(string $key): self
    {
        return new self($key);
    }

    /**
     * Shared Key for Authenticated Symmetric Key Encryption
     *
     * @return non-empty-string
     */
    public function shared(): string
    {
        return \sodium_crypto_generichash($this->key, '', \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES);
    }

    /**
     * Secret Key for Asymmetric Key Signature Authentication
     *
     * @return non-empty-string
     */
    public function secret(): string
    {
        $seed = \sodium_crypto_generichash($this->key, '', \SODIUM_CRYPTO_SIGN_SEEDBYTES);
        $key_pair = \sodium_crypto_sign_seed_keypair($seed);
        return \sodium_crypto_sign_secretkey($key_pair);
    }

    /**
     * Public Key for Asymmetric Key Signature Authentication
     *
     * @return non-empty-string
     */
    public function public(): string
    {
        $seed = \sodium_crypto_generichash($this->key, '', \SODIUM_CRYPTO_SIGN_SEEDBYTES);
        $key_pair = \sodium_crypto_sign_seed_keypair($seed);
        return \sodium_crypto_sign_publickey($key_pair);
    }
}
