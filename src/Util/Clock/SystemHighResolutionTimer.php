<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Clock;

final readonly class SystemHighResolutionTimer implements HighResolutionTimer
{
    /**
     * Returns the system's high resolution time in nanoseconds counted from an
     * arbitrary point in time, e.g. system restart. The delivered timestamp is
     * monotonic and cannot be adjusted.
     */
    #[\Override]
    public function now(): int
    {
        return (int)\hrtime(true);
    }
}
