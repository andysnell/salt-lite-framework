<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Stream;

use PhoneBurner\SaltLite\Framework\Http\Stream\IteratorStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IteratorStreamTest extends TestCase
{
    private const array METADATA_KEYS = [
        'wrapper_type',
        'stream_type',
        'mode',
        'unread_bytes',
        'seekable',
        'uri',
        'timed_out',
        'blocked',
        'eof',
    ];

    #[DataProvider('providesIterables')]
    #[Test]
    public function IteratorStream_has_expected_stream_behavior(
        iterable $iterable,
        string $expected,
    ): void {
        $stream = new IteratorStream($iterable);

        self::assertFalse($stream->isSeekable());
        self::assertFalse($stream->isWritable());
        self::assertTrue($stream->isReadable());
        self::assertNull($stream->getSize());
        self::assertSame([], $stream->getMetadata());
        foreach (self::METADATA_KEYS as $key) {
            self::assertNull($stream->getMetadata($key));
        }

        self::assertFalse($stream->eof());
        self::assertSame(0, $stream->tell());

        $contents = '';
        while (! $stream->eof()) {
            $contents .= $stream->read(10);
        }

        self::assertSame($expected, $contents);
        self::assertSame(\strlen($expected), $stream->tell());
        self::assertTrue($stream->eof());

        // detach should be a no-op
        self::assertNull($stream->detach());
    }

    #[Test]
    public function rewind_resets_position(): void
    {
        $iterator = new \ArrayIterator(['foo', 'bar', 'baz']);
        $stream = new IteratorStream($iterator);

        self::assertSame('foobarbaz', (string)$stream);
        self::assertSame(9, $stream->tell());
        self::assertTrue($stream->eof());

        $stream->rewind();
        self::assertSame(0, $stream->tell());
        self::assertFalse($stream->eof());
        self::assertSame('foobarbaz', $stream->read());
    }

    #[Test]
    public function write_throws_exception(): never
    {
        $iterator = new \ArrayIterator(['foo', 'bar']);
        $stream = new IteratorStream($iterator);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot write to an iterator stream');
        $stream->write('baz');
    }

    #[Test]
    public function seek_throws_exception(): never
    {
        $iterator = new \ArrayIterator(['foo', 'bar']);
        $stream = new IteratorStream($iterator);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot seek an iterator stream');
        $stream->seek(0);
    }

    #[Test]
    public function getContents_returns_remaining_contents_string(): void
    {
        $iterator = new \ArrayIterator(['foo', 'bar', 'baz']);
        $stream = new IteratorStream($iterator);

        self::assertSame('foo', $stream->read(3));
        self::assertSame(3, $stream->tell());
        self::assertFalse($stream->eof());
        self::assertSame('barbaz', $stream->getContents());
        self::assertSame(9, $stream->tell());
        self::assertTrue($stream->eof());
    }

    #[DataProvider('providesIterables')]
    #[Test]
    public function getContents_happy_path(iterable $iterable, string $expected): void
    {
        $stream = new IteratorStream($iterable);

        self::assertSame($expected, $stream->getContents());
        self::assertSame(\strlen($expected), $stream->tell());
        self::assertTrue($stream->eof());
    }

    #[Test]
    public function toString_rewinds_and_returns_contents(): void
    {
        $iterator = new \ArrayIterator(['foo', 'bar', 'baz']);
        $stream = new IteratorStream($iterator);

        self::assertSame('foo', $stream->read(3));
        self::assertSame(3, $stream->tell());
        self::assertFalse($stream->eof());
        self::assertSame('foobarbaz', (string)$stream);
        self::assertSame(9, $stream->tell());
        self::assertTrue($stream->eof());
    }

    #[DataProvider('providesIterables')]
    #[Test]
    public function toString_happy_path(iterable $iterable, string $expected): void
    {
        $stream = new IteratorStream($iterable);

        self::assertSame($expected, (string)$stream);
        self::assertSame(\strlen($expected), $stream->tell());
        self::assertTrue($stream->eof());
    }

    public static function providesIterables(): \Generator
    {
        $expected = '';
        $values = [];
        while (\strlen($expected) < 8192 * 100) {
            $value = \random_bytes(\random_int(8192, 8192 * 10)) . \PHP_EOL;
            $values[] = $value;
            $expected .= $value;
        }

        $single_value = \random_bytes(8192 * 3);

        $tests = [
            'Empty' => ['values' => [], 'expected' => ''],
            'Short' => ['values' => ['foo', 'bar', 'baz'], 'expected' => 'foobarbaz'],
            'SingleValue' => ['values' => [$single_value], 'expected' => $single_value],
            'Big' => ['values' => $values, 'expected' => $expected],
        ];

        foreach ($tests as $name => ['values' => $values, 'expected' => $expected]) {
            yield $name . 'Array' => [$values, $expected];

            yield $name . 'Iterator' => [new \ArrayIterator($values), $expected];

            yield $name . 'IteratorIterator' => [new \IteratorIterator(new \ArrayIterator($values)), $expected];

            yield $name . 'Generator' => [(static function () use ($values) {
                yield from $values;
            })(), $expected];

            yield $name . 'IteratorAggregate' => [new class ($values) implements \IteratorAggregate {
                public function __construct(private readonly array $values)
                {
                }

                public function getIterator(): \Traversable
                {
                    return yield from $this->values;
                }
            }, $expected];
        }
    }
}
