<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\EventDispatcher\Command\DebugEventListenersCommand;
use PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener\LogEventWasDispatched;
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
            DebugEventListenersCommand::class,
            static fn(App $app): DebugEventListenersCommand => new DebugEventListenersCommand(
                $app->get(SymfonyEventDispatcherInterface::class),
            ),
        );
    }
}
