<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;
use PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener\LazyListener;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

class EventDispatcherServiceFactory implements ServiceFactory
{
    private array $cache = [];

    public function __invoke(App $app, string $id): EventDispatcher
    {
        try {
            return ghost(function (EventDispatcher $ghost) use ($app): void {
                $ghost->__construct();

                foreach ($app->config->get('event_dispatcher.subscribers') ?: [] as $subscriber) {
                    \assert(Type::isClassStringOf(EventSubscriberInterface::class, $subscriber));
                    foreach ($subscriber::getSubscribedEvents() as $event => $methods) {
                        $this->registerSubscriberListeners($app, $ghost, $event, $subscriber, $methods);
                    }
                }

                foreach ($app->config->get('event_dispatcher.listeners') ?: [] as $event => $listeners) {
                    foreach ($listeners as $listener) {
                        $ghost->addListener($event, $this->listener($app, $listener));
                    }
                }
            });
        } finally {
            $this->cache = [];
        }
    }

    private function listener(
        App $app,
        string $listener_class,
        string|null $listener_method = null,
    ): callable {
        return $this->cache[$listener_class . '.' . $listener_method] ??= self::resolve(
            $app,
            $listener_class,
            $listener_method,
        );
    }

    private static function resolve(
        App $app,
        string $listener_class,
        string|null $listener_method = null,
    ): callable {
        \assert(\class_exists($listener_class) || \interface_exists($listener_class));
        $reflection = new \ReflectionClass($listener_class);
        if (! $reflection->isInstantiable()) {
            return new LazyListener($app, $listener_class, $listener_method);
        }

        $proxy = $reflection->newLazyProxy(static fn(object $object): object => $reflection->initializeLazyObject(
            $app->get($object::class),
        ));

        if ($listener_method !== null) {
            return $proxy->$listener_method(...);
        }

        \assert(\is_callable($proxy));
        return $proxy;
    }

    private function registerSubscriberListeners(
        App $app,
        EventDispatcher $dispatcher,
        string $event,
        string $subscriber,
        array|string $methods,
    ): void {
        match (true) {
            \is_string($methods) => $dispatcher->addListener($event, $this->listener(
                $app,
                $subscriber,
                $methods,
            )),
            \is_string($methods[0]) => $dispatcher->addListener($event, $this->listener(
                $app,
                $subscriber,
                $methods[0],
            ), $methods[1] ?? 0),
            default => \array_walk($methods, fn(array|string $methods): null => $this->registerSubscriberListeners(
                $app,
                $dispatcher,
                $event,
                $subscriber,
                $methods,
            )),
        };
    }
}
