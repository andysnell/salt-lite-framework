<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Clock;

use Carbon\CarbonImmutable;
use PhoneBurner\SaltLite\Framework\App\AppServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use Psr\Clock\ClockInterface;

#[DefaultServiceProvider(AppServiceProvider::class)]
interface Clock extends ClockInterface
{
    /**
     * Returns the current time as a DateTimeImmutable object, as required by the
     * proposed PSR, more specifically, an instance of CarbonImmutable
     */
    public function now(): CarbonImmutable;
}
