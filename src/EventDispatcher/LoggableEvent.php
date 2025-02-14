<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Framework\Logging\LogLevel;

interface LoggableEvent
{
    public function getLogLevel(): LogLevel;

    public function getLogMessage(): \Stringable|string;

    public function getLogContext(): array;
}
