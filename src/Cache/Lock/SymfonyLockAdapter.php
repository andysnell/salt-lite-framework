<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Domain\Time\StopWatch;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\SharedLockInterface;

#[Internal]
class SymfonyLockAdapter implements Lock, LoggerAwareInterface
{
    public function __construct(private readonly SharedLockInterface $lock)
    {
    }

    #[\Override]
    public function acquire(
        bool $blocking = false,
        int $timeout_seconds = 300,
        int $delay_microseconds = 250_000,
        SharedLockMode $mode = SharedLockMode::Write,
    ): bool {
        $timer = StopWatch::start();
        do {
            $acquired = match ($mode) {
                SharedLockMode::Write => $this->lock->acquire(false),
                SharedLockMode::Read => $this->lock->acquireRead(false),
            };

            if ($acquired || $blocking === false) {
                return $acquired;
            }

            \usleep($delay_microseconds);
        } while ($timer->elapsed()->inSeconds() < $timeout_seconds);

        return false;
    }

    #[\Override]
    public function release(): void
    {
        $this->lock->release();
    }

    #[\Override]
    public function refresh(Ttl|null $ttl = null): void
    {
        $this->lock->refresh($ttl instanceof Ttl ? $ttl->seconds : $ttl);
    }

    #[\Override]
    public function acquired(): bool
    {
        return $this->lock->isAcquired();
    }

    #[\Override]
    public function ttl(): Ttl|null
    {
        $ttl = $this->lock->getRemainingLifetime();
        return $ttl !== null ? new Ttl($ttl) : null;
    }

    #[\Override]
    public function setLogger(LoggerInterface $logger): void
    {
        if ($this->lock instanceof LoggerAwareInterface) {
            $this->lock->setLogger($logger);
        }
    }

    public function wrapped(): SharedLockInterface
    {
        return $this->lock;
    }
}
