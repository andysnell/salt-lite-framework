<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKey;
use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKeyFactory;
use PhoneBurner\SaltLite\Framework\Cache\Lock\SymfonyLockAdapter;
use PhoneBurner\SaltLite\Framework\Cache\Lock\SymfonyLockFactoryAdapter;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\LockFactory as SymfonyLockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class SymfonyLockFactoryAdapterTest extends TestCase
{
    #[Test]
    public function lock_factory_sets_logger_on_wrapped_factory(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $symfony_lock_factory = $this->createMock(SymfonyLockFactory::class);
        $symfony_lock_factory->expects(self::once())->method('setLogger')->with($logger);

        $lock_factory = new SymfonyLockFactoryAdapter(new NamedKeyFactory(), $symfony_lock_factory);

        $lock_factory->setLogger($logger);
    }

    #[DataProvider('providesTests')]
    #[Test]
    public function lock_factory_creates_locks(
        Key|\Stringable|string $key,
        bool $auto_release,
        int|float $ttl,
    ): void {
        $symfony_lock_factory = $this->createMock(SymfonyLockFactory::class);
        $symfony_lock_factory->expects(self::once())
            ->method('createLockFromKey')
            ->with(new Key('locks.test_resource_key'), $ttl, $auto_release)
            ->willReturn($this->createMock(SharedLockInterface::class));

        $lock_factory = new SymfonyLockFactoryAdapter(new NamedKeyFactory(), $symfony_lock_factory);

        $lock = $lock_factory->make($key, Ttl::seconds($ttl), $auto_release);

        self::assertInstanceOf(SymfonyLockAdapter::class, $lock);
    }

    public static function providesTests(): \Generator
    {
        $keys = [
            'TestResourceKey',
            new NamedKey('test_resource_key'),
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'TestResourceKey';
                }
            },
        ];

        foreach ($keys as $key) {
            foreach ([true, false] as $auto_release) {
                foreach ([0, 1, 250, \PHP_INT_MAX] as $ttl) {
                    yield [$key, $auto_release, $ttl];
                }
            }
        }
    }
}
