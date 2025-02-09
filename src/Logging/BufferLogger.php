<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * A PSR-3 logger implementation that buffers log entries in memory. This can be
 * used for testing or in places where you want to defer logging until later. For
 * example, this can be used as a default logger class on objects instantiated
 * before the actual logger can safely be resolved from the container. When the
 * real logger is available, the buffer can be written to it.
 */
class BufferLogger implements LoggerInterface, \Countable
{
    use LoggerTrait;

    /**
     * @var list<LogEntry>
     */
    private array $entries = [];

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->entries[] = new LogEntry(LogLevel::cast($level), $message, $context);
    }

    /**
     * Clear the buffer and return any log entries that were still present in the
     * buffer.
     *
     * @return list<LogEntry>
     */
    public function clear(): array
    {
        $entries = $this->entries;
        $this->entries = [];
        return $entries;
    }

    public function read(int|null $max = null): array
    {
        if ($this->entries === []) {
            return [];
        }

        if ($max === null || $max >= \count($this->entries)) {
            $entries = $this->entries;
            $this->entries = [];
            return $entries;
        }

        $read_entries = \array_slice($this->entries, 0, $max);
        $this->entries = \array_slice($this->entries, $max);

        return $read_entries;
    }

    public function write(LogEntry ...$entries): void
    {
        foreach ($entries as $entry) {
            $this->entries[] = $entry;
        }
    }

    public function count(): int
    {
        return \count($this->entries);
    }

    /**
     * Copy all buffered log entries to another logger. Does not clear the buffer.
     */
    public function copy(LoggerInterface $logger): void
    {
        foreach ($this->entries as $entry) {
            $logger->log($entry->level->value, $entry->message, $entry->context);
        }
    }
}
