<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Clock;

use Carbon\CarbonImmutable;
use PhoneBurner\SaltLite\Framework\App\Clock\Clock;

class SystemClock implements Clock
{
    #[\Override]
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }
}
