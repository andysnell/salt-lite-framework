<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyContractsEventDispatcherInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class EventDispatcherServiceProvider implements ServiceProvider
{
    public static function bind(): array
    {
        return [
            EventDispatcherInterface::class => EventDispatcher::class,
            SymfonyEventDispatcherInterface::class => EventDispatcher::class,
            SymfonyContractsEventDispatcherInterface::class => EventDispatcher::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(EventDispatcher::class, new EventDispatcherServiceFactory());
    }
}
