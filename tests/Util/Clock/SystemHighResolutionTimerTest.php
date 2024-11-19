<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Clock;

use PhoneBurner\SaltLite\Framework\Util\Clock\SystemHighResolutionTimer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SystemHighResolutionTimerTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $timer = new SystemHighResolutionTimer();
        $now = $timer->now();
        for ($i = 0; $i < 10000; ++$i) {
            self::assertGreaterThan($now, $now = $timer->now());
        }
    }
}
