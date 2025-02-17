<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time;

use PhoneBurner\SaltLite\Framework\Util\Helper\Math;

final readonly class Ttl
{
    public const int DEFAULT_SECONDS = 5 * TimeConstant::SECONDS_IN_MINUTE;

    public function __construct(public int|float $seconds = self::DEFAULT_SECONDS)
    {
        $seconds >= 0 || throw new \UnexpectedValueException('TTL must be greater than or equal to 0');
    }

    /**
     * This named constructor is designed to be backwards compatible with the
     * various ways a time-to-live value can be referenced from both of the PSRs
     * concerned with caching, and our legacy Util_Memcache class.
     */
    public static function make(mixed $ttl, \DateTimeImmutable $now = new \DateTimeImmutable()): self
    {
        return match (true) {
            $ttl instanceof self => $ttl,
            $ttl instanceof \DateInterval => self::until($now->add($ttl), $now),
            $ttl instanceof \DateTimeInterface => self::until($ttl, $now),
            $ttl === null => self::max(),
            \is_int($ttl), \is_float($ttl) => new self($ttl),
            \is_numeric($ttl) => new self((float)$ttl),
            default => throw new \UnexpectedValueException('Cannot Convert Value to TTL'),
        };
    }

    public static function seconds(int|float $seconds = self::DEFAULT_SECONDS): self
    {
        return new self($seconds);
    }

    public static function minutes(int|float $minutes = 5): self
    {
        return new self($minutes * TimeConstant::SECONDS_IN_MINUTE);
    }

    public static function hours(int $hours = 1): self
    {
        return new self($hours * TimeConstant::SECONDS_IN_HOUR);
    }

    public static function days(int $days = 1): self
    {
        return new self($days * TimeConstant::SECONDS_IN_DAY);
    }

    public static function until(\DateTimeInterface $datetime, \DateTimeInterface $now = new \DateTimeImmutable()): self
    {
        return new self($datetime->getTimestamp() - $now->getTimestamp());
    }

    public static function max(): self
    {
        static $max;
        return $max ??= new self(\PHP_INT_MAX);
    }

    public static function min(): self
    {
        static $min;
        return $min ??= new self(0);
    }

    public function inSeconds(): int
    {
        return (int)$this->seconds;
    }

    public function inMinutes(): int
    {
        return Math::floor($this->inSeconds() / TimeConstant::SECONDS_IN_MINUTE);
    }

    public function inHours(): int
    {
        return Math::floor($this->inSeconds() / TimeConstant::SECONDS_IN_HOUR);
    }

    public function __serialize(): array
    {
        return [$this->seconds];
    }

    public function __unserialize(array $data): void
    {
        [$this->seconds] = $data;
    }
}
