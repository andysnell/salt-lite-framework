<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\App\Clock;

use Carbon\CarbonImmutable;
use PhoneBurner\SaltLite\Framework\App\Clock\StaticClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StaticClockTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $now = CarbonImmutable::now();

        $clock = new StaticClock($now);

        $this->assertSame($now, $clock->now());
        \sleep(1);
        $this->assertSame($now, $clock->now());
    }
}
