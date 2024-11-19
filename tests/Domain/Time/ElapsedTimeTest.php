<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Time;

use PhoneBurner\SaltLite\Framework\Domain\Time\ElapsedTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ElapsedTimeTest extends TestCase
{
    #[Test]
    public function it_converts_to_seconds_correctly(): void
    {
        $elapsed = new ElapsedTime(12523432372);
        self::assertSame(12.5234, $elapsed->inSeconds());
        self::assertSame(12.52, $elapsed->inSeconds(2));
        self::assertSame(13.0, $elapsed->inSeconds(0));
        self::assertSame(12.523432, $elapsed->inSeconds(6));
    }

    #[Test]
    public function it_converts_to_milliseconds_correctly(): void
    {
        $elapsed = new ElapsedTime(12523432372);
        self::assertSame(12523.43, $elapsed->inMilliseconds());
        self::assertSame(12523.43, $elapsed->inMilliseconds(2));
        self::assertSame(12523.0, $elapsed->inMilliseconds(0));
        self::assertSame(12523.432372, $elapsed->inMilliseconds(6));
    }

    #[Test]
    public function it_converts_to_microseconds_correctly(): void
    {
        $elapsed = new ElapsedTime(323732);
        self::assertSame(324.0, $elapsed->inMicroseconds());
        self::assertSame(323.73, $elapsed->inMicroseconds(2));
        self::assertSame(324.0, $elapsed->inMicroseconds(0));
        self::assertSame(323.732, $elapsed->inMicroseconds(6));
    }

    #[Test]
    public function it_converts_to_string_correctly(): void
    {
        $elapsed = new ElapsedTime(12523432372);
        self::assertSame('12.5234', (string)$elapsed);
    }
}
