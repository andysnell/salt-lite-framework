<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\EventDispatcher\Command\DebugEventListeners;
use PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener\LogEventWasDispatched;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
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
            EventDispatcherInterface::class => SymfonyEventDispatcherAdapter::class,
            SymfonyEventDispatcherInterface::class => SymfonyEventDispatcherAdapter::class,
            SymfonyContractsEventDispatcherInterface::class => SymfonyEventDispatcherAdapter::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(SymfonyEventDispatcherAdapter::class, new EventDispatcherServiceFactory());
        $app->set(
            LogEventWasDispatched::class,
            static fn(App $app): LogEventWasDispatched => new LogEventWasDispatched(
                $app->get(LoggerInterface::class),
            ),
        );

        $app->set(
            DebugEventListeners::class,
            static fn(App $app): DebugEventListeners => new DebugEventListeners(
                $app->get(SymfonyEventDispatcherInterface::class),
            ),
        );
    }
}
