<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Iterator;

use PhoneBurner\SaltLite\Framework\Http\Domain\Stream\IteratorStream;
use Psr\Http\Message\StreamInterface;

/**
 * Iterates a PSR-7 Psr\Http\Message\StreamInterface
 *
 * @implements \Iterator<int, string>
 * @see IteratorStream
 * The somewhat-related StreamInterface implementation that wraps an iterator.
 */
class StreamIterator implements \Iterator
{
    public const int CHUNK_BYTES = 8192;

    private int $bytes = 0;

    private string|null $buffer = null;

    public function __construct(
        private readonly StreamInterface $stream,
        private readonly int $chunk_size = self::CHUNK_BYTES,
    ) {
    }

    public function current(): string
    {
        return $this->buffer ?? throw new \LogicException(
            'current() called in invalid state',
        );
    }

    public function key(): int
    {
        return $this->bytes;
    }

    public function next(): void
    {
        if ($this->stream->eof()) {
            $this->buffer = null;
            return;
        }

        $this->buffer = $this->stream->read($this->chunk_size);
        $this->bytes += \strlen($this->buffer);
    }

    public function valid(): bool
    {
        return $this->buffer !== null;
    }

    public function rewind(): void
    {
        if ($this->stream->isSeekable()) {
            $this->stream->rewind();
        }

        if ($this->stream->eof()) {
            throw new \LogicException('Cannot Rewind Non-Seekable Stream and Stream at EOF');
        }

        $this->bytes = 0;
        $this->buffer = '';
    }
}
