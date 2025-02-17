<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Iterator;

use PhoneBurner\SaltLite\Framework\Http\Domain\Stream\InMemoryStream;
use PhoneBurner\SaltLite\Framework\Util\Iterator\StreamIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StreamIteratorTest extends TestCase
{
    #[Test]
    public function happy_path_seekable(): void
    {
        $content = \random_bytes(8192 * 10);

        $stream = new InMemoryStream();
        $stream->write($content);

        $iterator = new StreamIterator($stream, 1000);

        $bytes = '';
        $total_length = 0;
        foreach ($iterator as $chunk) {
            $chunk_length = \strlen($chunk);
            $total_length += $chunk_length;
            $bytes .= $chunk;

            self::assertSame($total_length, $iterator->key());
            self::assertLessThanOrEqual(1000, $chunk_length);
        }

        self::assertSame($content, $bytes);

        $stream->rewind();

        $bytes = '';
        $total_length = 0;
        foreach ($iterator as $chunk) {
            $chunk_length = \strlen($chunk);
            $total_length += $chunk_length;
            $bytes .= $chunk;

            self::assertSame($total_length, $iterator->key());
            self::assertLessThanOrEqual(1000, $chunk_length);
        }

        self::assertSame($content, $bytes);
    }

    #[Test]
    public function happy_path_nonseekable(): void
    {
        $content = \random_bytes(8192 * 10);

        $stream = new class extends InMemoryStream {
            protected bool $seekable = false;
        };
        $stream->write($content);
        $stream->rewind();

        $iterator = new StreamIterator($stream, 1000);

        $bytes = '';
        $total_length = 0;
        foreach ($iterator as $chunk) {
            $chunk_length = \strlen($chunk);
            $total_length += $chunk_length;
            $bytes .= $chunk;

            self::assertSame($total_length, $iterator->key());
            self::assertLessThanOrEqual(1000, $chunk_length);
        }

        self::assertSame($content, $bytes);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot Rewind Non-Seekable Stream and Stream at EOF');
        foreach ($iterator as $chunk) {
            // noop
        }
    }
}
