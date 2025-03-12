<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Lock;

use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Time\Ttl;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory as SymfonyLockFactory;

#[Internal]
class SymfonyLockFactoryAdapter implements LockFactory, LoggerAwareInterface
{
    public function __construct(
        private readonly SymfonyNamedKeyFactory $key_factory,
        private readonly SymfonyLockFactory $lock_factory,
    ) {
    }

    #[\Override]
    public function make(
        SymfonyNamedKey|\Stringable|string $key,
        Ttl $ttl = new Ttl(300),
        bool $auto_release = true,
    ): SymfonyLockAdapter {
        return new SymfonyLockAdapter($this->lock_factory->createLockFromKey(
            $key instanceof SymfonyNamedKey ? $key->key : $this->key_factory->make($key)->key,
            $ttl->seconds,
            $auto_release,
        ));
    }

    #[\Override]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->lock_factory->setLogger($logger);
    }
}
