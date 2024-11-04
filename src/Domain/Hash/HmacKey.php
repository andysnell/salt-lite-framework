<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Hash;

readonly class HmacKey implements \Stringable
{
    final private function __construct(public string $value)
    {
        if (\strlen($this->value) !== 64 || ! \ctype_xdigit($this->value)) {
            throw new \InvalidArgumentException("Expecting 256-bit hex string, got " . $this->value);
        }
    }

    public static function make(string|\Stringable $value): self
    {
        return new self(\strtolower((string)$value));
    }

    /**
     * Generates a 256-bit random key and returns it as a hex-encoded string.
     * This is the correct length for the Blake2b algorithm, and provides strong
     * security for the underlying HMAC signing algorithm used for MD5, SHA1, etc...
     */
    public static function generate(): self
    {
        return new self(\bin2hex(\sodium_crypto_generichash_keygen()));
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
