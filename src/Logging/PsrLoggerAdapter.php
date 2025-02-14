<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Wrap a PSR-3 LoggerInterface as a PSR-3 LoggerInterface, allowing us to use
 * our own LogLevel enum.
 */
class PsrLoggerAdapter extends AbstractLogger
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level instanceof LogLevel ? $level->value : $level, $message, $context);
    }
}
