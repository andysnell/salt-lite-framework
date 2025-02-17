<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Domain\Stream;

use PhoneBurner\SaltLite\Framework\Util\Iterator\StreamIterator;
use Psr\Http\Message\StreamInterface;

/**
 * A StreamInterface implementation that reads from an array or Traversable
 * instance to produce the stream data. Also, is an iterator itself.
 *
 * @implements \IteratorAggregate<string>
 */
class IteratorStream implements StreamInterface, \Stringable, \IteratorAggregate
{
    public const int CHUNK_BYTES = 8192;

    private readonly \Iterator $iterator;

    private int|null $position = null;

    private string $buffer = '';

    public function __construct(iterable $iterable)
    {
        $this->iterator = match (true) {
            $iterable instanceof \Iterator => $iterable,
            \is_array($iterable) => new \ArrayIterator($iterable),
            default => new \IteratorIterator($iterable),
        };
    }

    #[\Override]
    public function __toString(): string
    {
        $this->rewind();
        return $this->getContents();
    }

    #[\Override]
    public function close(): void
    {
        // noop
    }

    #[\Override]
    public function detach(): null
    {
        return null;
    }

    #[\Override]
    public function getSize(): null
    {
        return null;
    }

    #[\Override]
    public function tell(): int
    {
        return (int)$this->position;
    }

    /**
     * @phpstan-impure
     */
    #[\Override]
    public function eof(): bool
    {
        return $this->position !== null
            && $this->buffer === ''
            && ! $this->iterator->valid();
    }

    #[\Override]
    public function isSeekable(): false
    {
        return false;
    }

    /**
     * @throws \RuntimeException Required by the StreamInterface spec, even though
     * this would be a better fit for a \LogicException
     */
    #[\Override]
    public function seek(int $offset, int $whence = \SEEK_SET): never
    {
        throw new \RuntimeException('Cannot seek an iterator stream');
    }

    #[\Override]
    public function rewind(): void
    {
        $this->iterator->rewind();
        $this->buffer = '';
        $this->position = 0;
    }

    #[\Override]
    public function isWritable(): false
    {
        return false;
    }

    /**
     * @throws \RuntimeException Required by the StreamInterface spec, even though
     * this would be a better fit for a \LogicException
     */
    #[\Override]
    public function write(string $string): never
    {
        throw new \RuntimeException('Cannot write to an iterator stream');
    }

    #[\Override]
    public function isReadable(): true
    {
        return true;
    }

    #[\Override]
    public function read(int $length = self::CHUNK_BYTES): string
    {
        // IteratorIterator instances must be rewound before use, otherwise the
        // current value of the inner Traversable will not be returned when the
        // IteratorIterator::current() method is called, but we want to do this
        // as late as possible, since it will have side effects, caching the
        // current value of the inner Traversable.
        if ($this->position === null) {
            $this->rewind();
        }

        while ($this->iterator->valid() && \strlen($this->buffer) <= $length) {
            $this->buffer .= $this->iterator->current();
            $this->iterator->next();
        }

        $bytes = \substr($this->buffer, 0, $length);

        $this->position += \strlen($bytes);
        $this->buffer = \substr($this->buffer, $length);

        return $bytes;
    }

    #[\Override]
    public function getContents(): string
    {
        $contents = '';
        while (! $this->eof()) {
            $contents .= $this->read();
        }

        return $contents;
    }

    #[\Override]
    public function getMetadata(string|null $key = null): array|null
    {
        return $key === null ? [] : null;
    }

    public function getIterator(): StreamIterator
    {
        return new StreamIterator($this);
    }
}
