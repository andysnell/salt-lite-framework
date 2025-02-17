<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Domain\Stream;

use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileMode;
use PhoneBurner\SaltLite\Framework\Util\Helper\File;
use Psr\Http\Message\StreamInterface;

/**
 * StreamInterface optimized for a stream kept completely within memory
 */
class InMemoryStream implements \Stringable, StreamInterface
{
    public const int CHUNK_BYTES = 8192;

    protected bool $readable = true;

    protected bool $writable = true;

    protected bool $seekable = true;

    /**
     * @var resource|null
     */
    private mixed $stream;

    public function __construct()
    {
        $this->stream = File::open('php://memory', FileMode::ReadWriteCreateNewOnly);
    }

    public function __destruct()
    {
        $this->close();
    }

    public static function make(string $content = ''): self
    {
        $stream = new self();
        $stream->write($content);
        $stream->rewind();
        return $stream;
    }

    public function close(): void
    {
        $stream = $this->detach();
        if (\is_resource($stream)) {
            \fclose($stream);
        }
    }

    /**
     * @return resource|null
     */
    public function detach(): mixed
    {
        if ($this->stream === null) {
            return null;
        }

        $stream = $this->stream;
        $this->stream = null;
        $this->writable = false;
        $this->readable = false;
        $this->seekable = false;
        return $stream;
    }

    public function getSize(): int
    {
        return \fstat($this->stream())['size'] ?? 0;
    }

    public function tell(): int
    {
        $position = \ftell($this->stream());
        if ($position === false) {
            throw new \RuntimeException('Could not determine position');
        }
        return $position;
    }

    public function eof(): bool
    {
        return \feof($this->stream());
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    public function seek(int $offset, int $whence = \SEEK_SET): void
    {
        \fseek($this->stream(), $offset, $whence);
    }

    public function rewind(): void
    {
        \rewind($this->stream());
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function write(string $string): int
    {
        $bytes = \fwrite($this->stream(), $string);
        if ($bytes === false) {
            throw new \RuntimeException('Could not write to stream');
        }
        return $bytes;
    }

    public function isReadable(): bool
    {
        return $this->readable;
    }

    public function read(int $length = self::CHUNK_BYTES): string
    {
        $content = \fread($this->stream(), \max($length, 1));
        if ($content === false) {
            throw new \RuntimeException('Could not read from stream');
        }
        return $content;
    }

    public function getContents(): string
    {
        $contents = \stream_get_contents($this->stream());
        if ($contents === false) {
            throw new \RuntimeException('Could not read from stream');
        }

        return $contents;
    }

    public function getMetadata(string|null $key = null): mixed
    {
        if ($key === null) {
            return \stream_get_meta_data($this->stream());
        }

        return \stream_get_meta_data($this->stream())[$key] ?? null;
    }

    public function __toString(): string
    {
        $this->rewind();
        return $this->getContents();
    }

    /**
     * @return resource
     */
    private function stream(): mixed
    {
        return $this->stream ?? throw new \RuntimeException('Stream resource detached from instance');
    }
}
