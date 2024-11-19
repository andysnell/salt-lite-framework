<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Cache\Lock\NullLock;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullLockTest extends TestCase
{
    #[Test]
    public function default_values_are_sane_for_a_null_lock(): void
    {
        $sut = new NullLock();
        self::assertTrue($sut->acquire());
        self::assertTrue($sut->acquired());
        self::assertNull($sut->ttl());
    }

    #[Test]
    public function values_can_be_configured(): void
    {
        $sut = new NullLock(
            Ttl::seconds(34),
            false,
            false,
        );

        self::assertFalse($sut->acquire());
        self::assertFalse($sut->acquired());
        self::assertEquals(Ttl::seconds(34), $sut->ttl());
    }
}
