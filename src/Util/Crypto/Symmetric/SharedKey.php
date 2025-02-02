<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric;

use PhoneBurner\SaltLite\Framework\Util\Crypto\BaseKey;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class SharedKey extends BaseKey
{
    public const int LENGTH = \SODIUM_CRYPTO_STREAM_XCHACHA20_KEYBYTES;

    public function bytes(): string
    {
        return $this->value;
    }

    public static function length(): int
    {
        return self::LENGTH;
    }
}
