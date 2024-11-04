<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Clock;

interface HighResolutionTimer
{
    /**
     * Returns the system's high resolution time in nanoseconds counted from an
     * arbitrary point in time, e.g. system restart. The delivered timestamp is
     * monotonic and cannot be adjusted.
     */
    public function now(): int;
}
