<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Enum;

/**
 * @phpstan-require-implements \BackedEnum
 */
trait WithStringBackedInstanceStaticMethod
{
    /** Case Insensitive Matching */
    public static function instance(mixed $value): self
    {
        return self::cast($value) ?? throw new \InvalidArgumentException();
    }

    public static function cast(mixed $value): self|null
    {
        return match (true) {
            $value instanceof self, $value === null => $value,
            \is_string($value) => self::tryFrom($value)
                ?? \array_find(static::cases(), static fn(self $case): bool => \strcasecmp($case->value, $value) === 0),
            \is_int($value), $value instanceof \Stringable => self::tryFrom((string)$value),
            default => null,
        };
    }
}
