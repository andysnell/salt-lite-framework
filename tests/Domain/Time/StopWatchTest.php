<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Time;

use PhoneBurner\SaltLite\Framework\Domain\Time\StopWatch;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StopWatchTest extends TestCase
{
    #[Test]
    public function elapsed_returns_the_duration(): void
    {
        $stopwatch = StopWatch::start();
        static::assertLessThan(1, $stopwatch->elapsed()->inSeconds());

        \sleep(1);
        $elapsed = $stopwatch->elapsed();
        static::assertGreaterThanOrEqual(1, $elapsed->inSeconds());
        static::assertLessThan(1.2, $elapsed->inSeconds());

        \sleep(1);
        $elapsed = $stopwatch->elapsed();
        static::assertGreaterThanOrEqual(2, $elapsed->inSeconds());
        static::assertLessThan(2.2, $elapsed->inSeconds());
    }
}
