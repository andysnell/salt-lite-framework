<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

/**
 * DTO representing a PSR-3 log event without a time component.
 */
readonly class LogEntry
{
    public function __construct(
        public LogLevel $level,
        public \Stringable|string $message,
        public array $context = [],
    ) {
    }
}
