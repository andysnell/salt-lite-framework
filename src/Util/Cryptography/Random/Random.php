<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Random;

use Random\Engine\Secure;
use Random\IntervalBoundary;
use Random\Randomizer;
use UnitEnum as T;

/**
 * Dependency-injection-friendly random number generator that should allow for easy mocking in tests.
 */
class Random
{
    public const string NUMERIC_CHARS = '0123456789';
    public const string UPPER_ALPHA_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const string LOWER_ALPHA_CHARS = 'abcdefghijklmnopqrstuvwxyz';
    public const string ALPHA_CHARS = self::UPPER_ALPHA_CHARS . self::LOWER_ALPHA_CHARS;
    public const string ALPHANUMERIC_CHARS = self::NUMERIC_CHARS . self::ALPHA_CHARS;
    public const string HEX_CHARS = '0123456789abcdef';
    public const string SPECIAL_CHARS = '!@#$%^&*()_+-=[]{}|;:,.<>?';

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

    public function chars(int $length, string $chars = self::ALPHANUMERIC_CHARS): string
    {
        $length > 0 || throw new \UnexpectedValueException('length must be greater than 0');
        return $this->randomizer->getBytesFromString($chars, $length);
    }

    public function int(int $min = \PHP_INT_MIN, int $max = \PHP_INT_MAX): int
    {
        $min <= $max || throw new \UnexpectedValueException('bin must be less than or equal to max');
        return $this->randomizer->getInt($min, $max);
    }

    public function float(
        float $min = 0.0,
        float $max = 1.0,
        IntervalBoundary $boundary = IntervalBoundary::ClosedOpen,
    ): float {
        $min <= $max || throw new \UnexpectedValueException('min must be less than or equal to max');
        return $this->randomizer->getFloat($min, $max, $boundary);
    }

    /**
     * @return array<array-key>
     */
    public function keys(array $array, int $num): array
    {
        return ($num === 0 || $array === []) ? [] : $this->randomizer->pickArrayKeys($array, $num);
    }

    public function key(array $array): int|string|null
    {
        return $array === [] ? null : $this->randomizer->pickArrayKeys($array, 1)[0];
    }

    public function value(array $array): mixed
    {
        return $array === [] ? null : $array[$this->key($array)];
    }

    /**
     * Note: the array keys will be preserved in the returned array.
     *
     * @return ($preserve_keys is true ? array<array-key, mixed> : list<mixed>)
     */
    public function values(array $array, int $num, bool $preserve_keys = true): array
    {
        $values = [];
        foreach ($this->keys($array, $num) as $key) {
            $values[$key] = $array[$key];
        }
        return $preserve_keys ? $values : \array_values($values);
    }

    public function hex(int $bytes): string
    {
        $bytes > 0 || throw new \UnexpectedValueException('bytes must be greater than 0');
        return \bin2hex($this->randomizer->getBytes($bytes));
    }

    /**
     * @template T of \UnitEnum
     * @phpstan-param T|class-string<T> $enum_class
     */
    public function enum(\UnitEnum|string $enum_class): \UnitEnum
    {
        if (! \is_a($enum_class, \UnitEnum::class, true)) {
            throw new \InvalidArgumentException(\sprintf('Class %s is not a UnitEnum', $enum_class));
        }

        $cases = $enum_class::cases() ?: throw new \LogicException('Enum has no cases');
        $key = $this->randomizer->pickArrayKeys($enum_class::cases(), 1)[0];
        return $cases[$key];
    }
}
