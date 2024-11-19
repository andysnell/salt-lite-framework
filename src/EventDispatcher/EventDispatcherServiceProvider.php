<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class EventDispatcherServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->bind(EventDispatcherInterface::class, EventDispatcher::class);
        $container->set(
            EventDispatcher::class,
            static function (ContainerInterface $container): EventDispatcher {
                $dispatcher = new EventDispatcher();
                foreach ($container->get(Configuration::class)->get('events.listeners') ?: [] as $event => $listeners) {
                    foreach ($listeners as $listener) {
                        $dispatcher->addListener($event, new LazyListener($container, $listener));
                    }
                }

                foreach ($container->get(Configuration::class)->get('events.subscribers') ?: [] as $subscriber) {
                    \assert(\is_string($subscriber) && \is_a($subscriber, EventSubscriberInterface::class, true));
                    foreach ($subscriber::getSubscribedEvents() as $event => $methods) {
                        self::registerSubscriberListeners($container, $dispatcher, $event, $subscriber, $methods);
                    }
                }

                return $dispatcher;
            },
        );
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
