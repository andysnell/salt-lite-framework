<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Queue\Event;

use PhoneBurner\SaltLite\Framework\Queue\Job;

class JobHandlingComplete
{
    public function __construct(public readonly Job $job)
    {
    }
}
