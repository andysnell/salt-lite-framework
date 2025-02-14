<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use PhoneBurner\SaltLite\Framework\Logging\LogEntry;

interface Loggable
{
    public function getLogEntry(): LogEntry;
}
