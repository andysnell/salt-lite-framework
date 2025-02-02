<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Hash;

use PhoneBurner\SaltLite\Framework\Util\Crypto\BaseKey;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class HmacKey extends BaseKey
{
    public const int LENGTH = \SODIUM_CRYPTO_GENERICHASH_KEYBYTES;

    /**
     * Make a new HMAC key from a hex string.
     */
    public static function make(#[\SensitiveParameter] string|\Stringable $value): self
    {
        return new self(\strtolower((string)$value));
    }

    public function bytes(): string
    {
        return $this->value;
    }

    public static function length(): int
    {
        return self::LENGTH;
    }
}
