<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Filesystem;

/**
 * @implements \IteratorAggregate<string>
 */
class FileReader implements \Stringable, \IteratorAggregate
{
    public const int CHUNK_BYTES = 8192;

    private function __construct(private readonly string $filename)
    {
        if (! \file_exists($this->filename) || ! \is_readable($this->filename)) {
            throw new \InvalidArgumentException('File Not Readable: ' . $this->filename);
        }
    }

    public static function make(string|\Stringable $filename): self
    {
        return new self($filename instanceof \SplFileInfo ? (string)$filename->getRealPath() : (string)$filename);
    }

    /**
     * @return \Generator<string>
     */
    public function lines(): \Generator
    {
        $stream = \fopen($this->filename, 'rb') ?: throw new \RuntimeException('Could Not Open File: ' . $this->filename);
        try {
            while (($line = \fgets($stream)) !== false) {
                yield $line;
            }

            if (! \feof($stream)) {
                throw new \RuntimeException('Unexpected End of File: ' . $this->filename);
            }
        } finally {
            \fclose($stream);
        }
    }

    #[\Override]
    public function getIterator(): \Generator
    {
        $stream = \fopen($this->filename, 'rb') ?: throw new \RuntimeException('Could Not Open File: ' . $this->filename);
        while (! \feof($stream)) {
            yield (string)\fread($stream, self::CHUNK_BYTES);
        }
        \fclose($stream);
    }

    #[\Override]
    public function __toString(): string
    {
        return (string)\file_get_contents($this->filename);
    }
}
