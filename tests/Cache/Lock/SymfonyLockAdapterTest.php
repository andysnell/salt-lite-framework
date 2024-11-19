<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Cache\Lock\SharedLockMode;
use PhoneBurner\SaltLite\Framework\Cache\Lock\SymfonyLockAdapter;
use PhoneBurner\SaltLite\Framework\Domain\Time\StopWatch;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\SharedLockInterface;

final class SymfonyLockAdapterTest extends TestCase
{
    #[Test]
    public function adapter_sets_logger_on_wrapped_lock(): void
    {
        $symfony_lock = new class implements SharedLockInterface, LoggerAwareInterface {
            use LoggerAwareTrait;

            public function acquire(bool $blocking = false): bool
            {
                return false;
            }

            public function acquireRead(bool $blocking = false): bool
            {
                return false;
            }

            public function release(): void
            {
            }

            public function refresh(float|null $ttl = null): void
            {
            }

            public function isAcquired(): bool
            {
                return false;
            }

            public function isExpired(): bool
            {
                return false;
            }

            public function getRemainingLifetime(): float|null
            {
                return null;
            }

            public function getLogger(): LoggerInterface
            {
                return $this->logger ?? throw new \RuntimeException('Logger not set');
            }
        };

        $logger = $this->createMock(LoggerInterface::class);

        $lock = new SymfonyLockAdapter($symfony_lock);
        $lock->setLogger($logger);

        self::assertSame($logger, $symfony_lock->getLogger());
    }

    #[DataProvider('providesBooleanValues')]
    #[Test]
    public function adapter_acquires_write_lock_without_blocking(bool $acquired): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('acquire')->with(false)->willReturn($acquired);

        $adapter = new SymfonyLockAdapter($lock);
        $timer = StopWatch::start();
        $adapter->acquire(blocking: false, mode: SharedLockMode::Write);
        self::assertLessThan(25000, $timer->elapsed()->inMicroseconds());
    }

    #[DataProvider('providesBooleanValues')]
    #[Test]
    public function adapter_acquires_read_lock_without_blocking(bool $acquired): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('acquireRead')->with(false)->willReturn($acquired);

        $adapter = new SymfonyLockAdapter($lock);
        $timer = StopWatch::start();
        $adapter->acquire(blocking: false, mode: SharedLockMode::Read);
        self::assertLessThan(25000, $timer->elapsed()->inMicroseconds());
    }

    #[Test]
    public function adapter_acquires_successful_write_lock_with_blocking(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->exactly(3))
            ->method('acquire')
            ->with(false)
            ->willReturn(false, false, true);

        $adapter = new SymfonyLockAdapter($lock);
        $timer = StopWatch::start();
        $adapter->acquire(blocking: true, timeout_seconds: 5, mode: SharedLockMode::Write);
        self::assertGreaterThan(25000, $timer->elapsed()->inMicroseconds());
    }

    #[Test]
    public function adapter_acquires_successful_read_lock_with_blocking(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->exactly(3))
            ->method('acquireRead')
            ->with(false)
            ->willReturn(false, false, true);

        $adapter = new SymfonyLockAdapter($lock);
        $timer = StopWatch::start();
        $adapter->acquire(blocking: true, timeout_seconds: 5, mode: SharedLockMode::Read);
        self::assertGreaterThan(25000, $timer->elapsed()->inMicroseconds());
    }

    #[Test]
    public function adapter_times_out_on_write_lock_with_blocking(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->atLeast(2))
            ->method('acquire')
            ->with(false)
            ->willReturn(false);

        $adapter = new SymfonyLockAdapter($lock);
        $timer = StopWatch::start();
        $adapter->acquire(blocking: true, timeout_seconds: 1, mode: SharedLockMode::Write);
        self::assertGreaterThan(25000, $timer->elapsed()->inMicroseconds());
    }

    #[Test]
    public function adapter_times_out_on_read_lock_with_blocking(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->atLeast(2))
            ->method('acquireRead')
            ->with(false)
            ->willReturn(false);

        $adapter = new SymfonyLockAdapter($lock);
        $timer = StopWatch::start();
        $adapter->acquire(blocking: true, timeout_seconds: 1, mode: SharedLockMode::Read);
        self::assertGreaterThan(25000, $timer->elapsed()->inMicroseconds());
    }

    #[Test]
    public function adapter_releases_lock(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('release');

        $adapter = new SymfonyLockAdapter($lock);
        $adapter->release();
    }

    #[Test]
    public function adapter_refreshes_lock_with_ttl(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('refresh')->with(123.45);

        $adapter = new SymfonyLockAdapter($lock);
        $adapter->refresh(Ttl::seconds(123.45));
    }

    #[Test]
    public function adapter_refreshes_lock_without_ttl(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('refresh')->with(null);

        $adapter = new SymfonyLockAdapter($lock);
        $adapter->refresh(null);
    }

    #[DataProvider('providesBooleanValues')]
    #[Test]
    public function adapter_checks_if_lock_is_acquired(bool $is_acquired): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('isAcquired')->willReturn($is_acquired);

        $adapter = new SymfonyLockAdapter($lock);
        self::assertSame($is_acquired, $adapter->acquired());
    }

    #[DataProvider('providesTtlValues')]
    #[Test]
    public function adapter_returns_non_null_ttl(int|float $ttl): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('getRemainingLifetime')->willReturn($ttl);

        $adapter = new SymfonyLockAdapter($lock);

        self::assertEquals(new Ttl($ttl), $adapter->ttl());
    }

    #[Test]
    public function adapter_returns_null_ttl(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects($this->once())->method('getRemainingLifetime')->willReturn(null);

        $adapter = new SymfonyLockAdapter($lock);

        self::assertNull($adapter->ttl());
    }

    public static function providesBooleanValues(): \Generator
    {
        yield [true];
        yield [false];
    }

    public static function providesTtlValues(): \Generator
    {
        yield [300.0];
        yield [30.25];
        yield [0.0];
    }
}
