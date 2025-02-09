<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Clock;

use Carbon\CarbonImmutable;
use PhoneBurner\SaltLite\Framework\App\Clock\Clock;

class StaticClock implements Clock
{
    public function __construct(private readonly CarbonImmutable $now)
    {
    }

    #[\Override]
    public function now(): CarbonImmutable
    {
        return $this->now;
    }
}
