<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Enum;

/**
 * @phpstan-require-implements \UnitEnum
 */
trait WithUnitEnumInstanceStaticMethod
{
    public static function instance(mixed $value): self
    {
        return self::tryInstance($value) ?? throw new \InvalidArgumentException();
    }

    public static function tryInstance(mixed $value): self|null
    {
        static $cases = \array_change_key_case(\array_column(self::cases(), null, 'name'), \CASE_LOWER);
        return match (true) {
            $value instanceof self, $value === null => $value,
            \is_string($value) => $cases[\strtolower($value)] ?? null,
            default => null,
        };
    }
}
