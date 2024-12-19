<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Enum;

/**
 * @phpstan-require-implements \BackedEnum
 */
trait WithIntegerBackedInstanceStaticMethod
{
    public static function instance(mixed $value): self
    {
        return match (true) {
            $value instanceof self => $value,
            \is_numeric($value) => self::from((int)$value),
            default => throw new \InvalidArgumentException(),
        };
    }
}
