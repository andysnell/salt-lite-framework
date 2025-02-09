<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener\LazyListener;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Reflect;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
        $app->set(EventDispatcher::class, static function (App $app): EventDispatcher {
            return Reflect::ghost(EventDispatcher::class, static function (EventDispatcher $ghost) use ($app): void {
                $ghost->__construct();
                foreach ($app->config->get('event_dispatcher.subscribers') ?: [] as $subscriber) {
                    \assert(\is_string($subscriber) && \is_a($subscriber, EventSubscriberInterface::class, true));
                    foreach ($subscriber::getSubscribedEvents() as $event => $methods) {
                        self::registerSubscriberListeners($app->services, $ghost, $event, $subscriber, $methods);
                    }
                }

                foreach ($app->config->get('event_dispatcher.listeners') ?: [] as $event => $listeners) {
                    foreach ($listeners as $listener) {
                        $ghost->addListener($event, new LazyListener($app->services, $listener));
                    }
                }
            });
        });
    }

    private static function registerSubscriberListeners(
        ContainerInterface $container,
        EventDispatcher $dispatcher,
        string $event,
        string $subscriber,
        array|string $methods,
    ): void {
        if (\is_string($methods)) {
            $dispatcher->addListener($event, new LazyListener($container, $subscriber, $methods));
            return;
        }

        if (\is_string($methods[0])) {
            $dispatcher->addListener($event, new LazyListener($container, $subscriber, $methods[0]), $methods[1] ?? 0);
            return;
        }

        foreach ($methods as $method) {
            self::registerSubscriberListeners($container, $dispatcher, $event, $subscriber, $method);
        }
    }
}
