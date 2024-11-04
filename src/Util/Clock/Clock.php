<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Clock;

use Carbon\CarbonImmutable;
use Psr\Clock\ClockInterface;

interface Clock extends ClockInterface
{
    /**
     * Returns the current time as a DateTimeImmutable object, as required by the
     * proposed PSR, more specifically, an instance of CarbonImmutable
     */
    public function now(): CarbonImmutable;
}
