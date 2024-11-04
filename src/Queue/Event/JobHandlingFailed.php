<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Queue\Event;

use PhoneBurner\SaltLite\Framework\Queue\Job;

class JobHandlingFailed
{
    public function __construct(
        public readonly Job $job,
        public readonly \Throwable|null $exception = null,
    ) {
    }
}
