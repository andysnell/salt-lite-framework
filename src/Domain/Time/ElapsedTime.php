<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time;

readonly class ElapsedTime implements \Stringable
{
    public function __construct(public int $nanoseconds)
    {
    }

    public static function make(int $nanoseconds): self
    {
        return new self($nanoseconds);
    }

    protected function convert(int $conversion_factor, int $precision): float
    {
        return \round($this->nanoseconds / $conversion_factor, $precision);
    }

    public function inSeconds(int $precision = 4): float
    {
        return $this->convert(TimeConstant::NANOSECONDS_IN_SECOND, $precision);
    }

    public function inMilliseconds(int $precision = 2): float
    {
        return $this->convert(TimeConstant::NANOSECONDS_IN_MILLISECOND, $precision);
    }

    public function inMicroseconds(int $precision = 0): float
    {
        return $this->convert(TimeConstant::NANOSECONDS_IN_MICROSECOND, $precision);
    }

    #[\Override]
    public function __toString(): string
    {
        return (string)$this->inSeconds();
    }
}
