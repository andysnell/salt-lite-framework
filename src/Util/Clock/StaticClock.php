<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Clock;

use Carbon\CarbonImmutable;

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
