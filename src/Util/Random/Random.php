<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Random;

use Random\Engine\Secure;
use Random\Randomizer;
use UnitEnum;

/**
 * Dependency-injection-friendly random number generator that should allow for easy mocking in tests.
 */
class Random
{
    public function __construct(private readonly Randomizer $randomizer = new Randomizer(new Secure()))
    {
    }

    public static function make(Randomizer $randomizer = new Randomizer(new Secure())): self
    {
        return new self($randomizer);
    }

    public function bytes(int $bytes): string
    {
        $bytes > 0 || throw new \UnexpectedValueException('bytes must be greater than 0');
        return $this->randomizer->getBytes($bytes);
    }

    public function int(int $min = \PHP_INT_MIN, int $max = \PHP_INT_MAX): int
    {
        $min <= $max || throw new \UnexpectedValueException('bin must be less than or equal to max');
        return $this->randomizer->getInt($min, $max);
    }

    public function hex(int $bytes): string
    {
        $bytes > 0 || throw new \UnexpectedValueException('bytes must be greater than 0');
        return \bin2hex($this->randomizer->getBytes($bytes));
    }

    /**
     * @template T of UnitEnum
     * @phpstan-param T|class-string<T> $enum_class
     * @return T&UnitEnum
     */
    public function enum(UnitEnum|string $enum_class): UnitEnum
    {
        if (! \is_a($enum_class, UnitEnum::class, true)) {
            throw new \InvalidArgumentException(\sprintf('Class %s is not a UnitEnum', $enum_class));
        }

        $key = $this->randomizer->pickArrayKeys($enum_class::cases(), 1)[0]
            ?? throw new \LogicException('Enum has no cases');

        return $enum_class::cases()[$key];
    }
}
