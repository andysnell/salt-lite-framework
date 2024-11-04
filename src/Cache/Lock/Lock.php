<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface Lock
{
    /**
     * If the lock can not be acquired, the method returns false. The acquire()
     * method can be safely called repeatedly, even if the lock is already acquired.
     *
     * If blocking is true, the method will wait until the lock can be acquired,
     * up to the timeout_seconds, delaying retry attempts by delay_microseconds.
     */
    public function acquire(
        bool $blocking = false,
        int $timeout_seconds = 30,
        int $delay_microseconds = 250_000,
        SharedLockMode $mode = SharedLockMode::Write,
    ): bool;

    /**
     * Releases the lock, if acquired, allowing other processes to acquire it.
     *
     * This is safe to call even if the lock has not been acquired, in which case
     * it will not do anything, and existing lock state will be preserved.
     */
    public function release(): void;

    /**
     * Updates the remaining time-to-live on an acquired lock, passing null will
     * reset the TTL to the original value.
     */
    public function refresh(Ttl|null $ttl = null): void;

    /**
     * Used to check if an unexpired lock has been acquired by *this process*
     *
     * This method will not tell you if the lock is currently held by another
     * process, only if the current process has acquired the lock. That is only
     * indicated by the return value of the acquire() method.
     *
     * E.g. this would be called during a long-running process that might exceed
     * the lock TTL. If this returns false, it means another process might have
     * acquired the lock in the meantime, and it is no longer safe to
     * continue/commit the current process.
     */
    public function acquired(): bool;

    /**
     * Returns the remaining time-to-live of the lock, or null if the lock has
     * expired or has not been acquired.
     */
    public function ttl(): Ttl|null;
}
