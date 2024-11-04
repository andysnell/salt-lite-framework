<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone;

use Carbon\CarbonImmutable;
use Countable;
use DateTimeInterface;
use DateTimeZone;
use Generator;
use IteratorAggregate;
use PhoneBurner\SaltLite\Framework\Domain\PhpSerializable;
use Stringable;
use UnderflowException;

/**
 * @implements IteratorAggregate<DateTimeZone>
 * @implements PhpSerializable<array<DateTimeZone>>
 */
final readonly class TimeZoneCollection implements
    IteratorAggregate,
    Countable,
    TimeZoneCollectionAware,
    PhpSerializable,
    Stringable
{
    /**
     * @var array<DateTimeZone>
     */
    private array $time_zones;

    private function __construct(DateTimeZone ...$time_zones)
    {
        $this->time_zones = \array_values(\array_unique($time_zones, \SORT_REGULAR));
    }

    public static function make(DateTimeZone ...$time_zones): self
    {
        return new self(...$time_zones);
    }

    public function first(): DateTimeZone
    {
        if ($this->time_zones === []) {
            throw new UnderflowException('TimeZoneCollection is Empty');
        }

        return $this->time_zones[\array_key_first($this->time_zones)];
    }

    public function getMinOffsetTimeZone(DateTimeInterface $datetime): DateTimeZone|null
    {
        if ($this->time_zones === []) {
            return null;
        }

        $time_zones = self::sortByUtcOffset($this->time_zones, $datetime);

        return $time_zones[\array_key_first($time_zones)] ?? null;
    }

    public function getMaxOffsetTimeZone(DateTimeInterface $datetime): DateTimeZone|null
    {
        if ($this->time_zones === []) {
            return null;
        }

        $time_zones = self::sortByUtcOffset($this->time_zones, $datetime);

        return $time_zones[\array_key_last($time_zones)] ?? null;
    }

    public function getEarliestLocalTime(DateTimeInterface $datetime): CarbonImmutable|null
    {
        if ($this->time_zones === []) {
            return null;
        }

        $time_zones = self::sortByLocalTime($this->time_zones, $datetime);

        return CarbonImmutable::instance($datetime)->setTimezone(\reset($time_zones));
    }

    public function getLatestLocalTime(DateTimeInterface $datetime): CarbonImmutable|null
    {
        if ($this->time_zones === []) {
            return null;
        }

        $time_zones = self::sortByLocalTime($this->time_zones, $datetime);

        return CarbonImmutable::instance($datetime)->setTimezone(\end($time_zones));
    }

    #[\Override]
    public function getTimeZones(): self
    {
        return $this;
    }

    /**
     * @phpstan-assert-if-true DateTimeZone $this->getMinOffsetTimeZone()
     * @phpstan-assert-if-true DateTimeZone $this->getMaxOffsetTimeZone()
     * @phpstan-assert-if-true CarbonImmutable $this->getEarliestLocalTime()
     * @phpstan-assert-if-true CarbonImmutable $this->getLatestLocalTime()
     */
    #[\Override]
    public function count(): int
    {
        return \count($this->time_zones);
    }

    /**
     * @return Generator<DateTimeZone>
     */
    #[\Override]
    public function getIterator(): Generator
    {
        yield from $this->time_zones;
    }

    #[\Override]
    public function __toString(): string
    {
        if ($this->time_zones === []) {
            return '';
        }

        $time_zones = \array_map(static fn(DateTimeZone $tz): string => $tz->getName(), $this->time_zones);
        \sort($time_zones);
        return \implode('&', $time_zones);
    }

    #[\Override]
    public function __serialize(): array
    {
        return $this->time_zones;
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->__construct(...\array_map(TimeZoneFactory::make(...), $data));
    }

    /**
     * @param non-empty-array<DateTimeZone> $time_zones
     * @return non-empty-array<DateTimeZone>
     */
    private static function sortByUtcOffset(array $time_zones, DateTimeInterface $datetime): array
    {
        if (\count($time_zones) === 1) {
            return $time_zones;
        }

        \usort($time_zones, static function (DateTimeZone $a, DateTimeZone $b) use ($datetime): int {
            return $a->getOffset($datetime) <=> $b->getOffset($datetime);
        });

        return $time_zones;
    }

    /**
     * Note: sorting by local time is not the same as sorting by offset, because
     * compared offsets can be positive and negative, resulting in out of order
     * local times.
     *
     * @param non-empty-array<DateTimeZone> $time_zones
     * @return non-empty-array<DateTimeZone>
     */
    private static function sortByLocalTime(array $time_zones, DateTimeInterface $datetime): array
    {
        if (\count($time_zones) === 1) {
            return $time_zones;
        }

        $datetime = CarbonImmutable::instance($datetime);
        \usort($time_zones, static function (DateTimeZone $a, DateTimeZone $b) use ($datetime): int {
            $a = (int)$datetime->setTimezone($a)->format('Gis');
            $b = (int)$datetime->setTimezone($b)->format('Gis');
            return $a <=> $b;
        });

        return $time_zones;
    }
}
