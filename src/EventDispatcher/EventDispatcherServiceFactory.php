<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Console\EventListener\ConsoleErrorListener;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;
use PhoneBurner\SaltLite\Framework\EventDispatcher\EventListener\LazyListener;
use PhoneBurner\SaltLite\Framework\Logging\LogLevel;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\EventListener\AddErrorDetailsStampListener;
use Symfony\Component\Messenger\EventListener\DispatchPcntlSignalListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnCustomStopExceptionListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Scheduler\EventListener\DispatchSchedulerEventListener;

use function PhoneBurner\SaltLite\Framework\ghost;

class EventDispatcherServiceFactory implements ServiceFactory
{
    private const array FRAMEWORK_SUBSCRIBERS = [
            // Console Subscribers
            ConsoleErrorListener::class,

            // Messenger Subscribers
            AddErrorDetailsStampListener::class,
            DispatchPcntlSignalListener::class,
            SendFailedMessageForRetryListener::class,
            SendFailedMessageToFailureTransportListener::class,
            StopWorkerOnCustomStopExceptionListener::class,
            StopWorkerOnRestartSignalListener::class,

            // Scheduler Subscribers
            DispatchSchedulerEventListener::class,
    ];

    private array $cache = [];

    public function __invoke(App $app, string $id): SymfonyEventDispatcherAdapter
    {
        try {
            $event_dispatcher = ghost(function (EventDispatcher $ghost) use ($app): void {
                $ghost->__construct();

                $subscribers = \array_unique([
                    ...self::FRAMEWORK_SUBSCRIBERS,
                    ...$app->config->get('event_dispatcher.subscribers') ?: [],
                ]);

                foreach ($subscribers as $subscriber) {
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

            return new SymfonyEventDispatcherAdapter(
                $event_dispatcher,
                $app->get(LoggerInterface::class),
                LogLevel::tryInstance($app->config->get('event_dispatcher.event_dispatch_log_level')),
                LogLevel::tryInstance($app->config->get('event_dispatcher.event_failure_log_level')),
            );
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
        EventDispatcherInterface $dispatcher,
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
