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
        return self::tryInstance($value) ?? throw new \InvalidArgumentException();
    }

    public static function tryInstance(mixed $value): self|null
    {
        return match (true) {
            $value instanceof self, $value === null => $value,
            \is_numeric($value) => self::tryFrom((int)$value),
            default => null,
        };
    }
}
