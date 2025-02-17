<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\String;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;

final class ConstantTime
{
    /**
     * Constant time identity comparison of string-like values. Binary strings
     * are compared byte-wise, with special handling of null input value (i.e.
     * it always returns false, since the known value has to be a string|object).
     */
    public static function equals(
        \Stringable|BinaryString|string $known,
        \Stringable|BinaryString|string|null $input,
    ): bool {
        return $input !== null && \hash_equals(self::cast($known), self::cast($input));
    }

    public static function stringStartsWith(
        \Stringable|BinaryString|string $haystack,
        \Stringable|BinaryString|string $needle,
    ): bool {
        $needle = self::cast($needle);
        $haystack = self::cast($haystack);
        return \hash_equals($needle, \substr($haystack, 0, \strlen($needle)));
    }

    private static function cast(\Stringable|BinaryString|string $value): string
    {
        return $value instanceof BinaryString ? $value->bytes() : (string)$value;
    }
}
