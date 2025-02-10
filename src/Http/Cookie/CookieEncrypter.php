<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\Hmac;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\HmacKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Symmetric;

class CookieEncrypter
{
    public const string HKDF_INFO = 'cookie_prefix';

    public const int MIN_BYTES = Symmetric::MIN_CIPHERTEXT_BYTES;

    private readonly HmacKey $prefix_key;

    public function __construct(
        private readonly Symmetric $crypto,
        private readonly SharedKey $key,
    ) {
        $this->prefix_key = HmacKey::derive($this->key, self::HKDF_INFO);
    }

    public function encrypt(Cookie $cookie): Cookie
    {
        $prefix = Hmac::string($cookie->name, $this->prefix_key)->digest(Encoding::Base64NoPadding);
        $ciphertext = $this->crypto->encrypt($this->key, $cookie->value);
        return $cookie->withValue($prefix . $ciphertext);
    }

    public function decrypt(string $name, string $value): string|null
    {
        $prefix = Hmac::string($name, $this->prefix_key)->digest(Encoding::Base64NoPadding);
        if (! \str_starts_with($value, $prefix)) {
            return $value;
        }

        try {
            return $this->crypto->decrypt($this->key, \substr($value, \strlen($prefix)));
        } catch (\Throwable) { // Decryption failed
            return null;
        }
    }
}
