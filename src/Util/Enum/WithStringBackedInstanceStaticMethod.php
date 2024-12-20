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
        return match (true) {
            $value instanceof self => $value,
            \is_string($value) => self::tryFrom($value)
                ?? \array_find(static::cases(), static fn(self $case): bool => \strcasecmp($case->value, $value) === 0)
                ?? throw new \ValueError($value . ' is not a valid backing value for enum ' . static::class),
            \is_int($value), $value instanceof \Stringable => self::from((string)$value),
            default => throw new \InvalidArgumentException(),
        };
    }
}
