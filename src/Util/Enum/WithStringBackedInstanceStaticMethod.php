<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Enum;

/**
 * @phpstan-require-implements \BackedEnum
 */
trait WithStringBackedInstanceStaticMethod
{
    public static function instance(mixed $value): self
    {
        return match (true) {
            $value instanceof self => $value,
            \is_string($value) => self::from($value),
            \is_int($value), $value instanceof \Stringable => self::from((string)$value),
            default => throw new \InvalidArgumentException(),
        };
    }
}
