<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto;

final readonly class AppKey implements \Stringable
{
    public const int LENGTH = \SODIUM_CRYPTO_AUTH_BYTES; // 256-bit key

    public string $value;

    public function __construct(#[\SensitiveParameter] string $value)
    {
        if (\str_starts_with($value, 'base64:')) {
            $value = \base64_decode(\substr($value, 7));
        }

        if (\strlen($value) !== self::LENGTH) {
            throw new \InvalidArgumentException('Invalid Key Length');
        }

        $this->value = $value;
    }

    public function id(): string
    {
        return 'blake2b:' . \bin2hex(\sodium_crypto_generichash($this->value));
    }

    public function encoded(): string
    {
        return 'base64:' . \base64_encode($this->value);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    public static function generate(): self
    {
        return new self(\random_bytes(self::LENGTH));
    }
}
