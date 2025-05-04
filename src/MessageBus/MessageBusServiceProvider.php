<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Container\ObjectContainer\ImmutableObjectContainer;
use PhoneBurner\SaltLite\Container\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\SaltLite\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionProvider;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\MessageBus\Config\BusConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\Config\MessageBusConfigStruct;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\MessageBusContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\ReceiverContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\SenderContainer;
use PhoneBurner\SaltLite\Framework\MessageBus\EventListener\LogWorkerMessageFailedEvent;
use PhoneBurner\SaltLite\Framework\MessageBus\EventListener\ResetServicesListener;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\AmazonSqsTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\AmqpTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\ContainerTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\DoctrineTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\InMemoryTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\RedisTransportFactory;
use PhoneBurner\SaltLite\Framework\MessageBus\TransportFactory\SyncTransportFactory;
use PhoneBurner\SaltLite\MessageBus\Handler\InvokableMessageHandler;
use PhoneBurner\SaltLite\MessageBus\MessageBus;
use PhoneBurner\SaltLite\Time\Clock\Clock;
use Psr\Cache\CacheItemPoolInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Command\DebugCommand;
use Symfony\Component\Messenger\Command\StatsCommand;
use Symfony\Component\Messenger\Command\StopWorkersCommand;
use Symfony\Component\Messenger\EventListener\AddErrorDetailsStampListener;
use Symfony\Component\Messenger\EventListener\DispatchPcntlSignalListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnCustomStopExceptionListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\RedispatchMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

use function PhoneBurner\SaltLite\Framework\ghost;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class MessageBusServiceProvider implements ServiceProvider
{
    public static function bind(): array
    {
        return [
            MessageBusInterface::class => SymfonyMessageBusAdapter::class,
            MessageBus::class => SymfonyMessageBusAdapter::class,
            RoutableMessageBus::class => SymfonyRoutableMessageBusAdapter::class,
            TransportFactory::class => ContainerTransportFactory::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            MessageBusContainer::class,
            static fn(App $app): MessageBusContainer => new MessageBusContainer(\array_map(
                static fn (BusConfigStruct $bus): SymfonyMessageBusAdapter => ghost(
                    static fn(SymfonyMessageBusAdapter $ghost): null => $ghost->__construct(
                        \array_map($app->services->get(...), $bus->middleware),
                    ),
                ),
                $app->get(MessageBusConfigStruct::class)->bus,
            )),
        );

        $app->set(
            SymfonyMessageBusAdapter::class,
            static fn(App $app): SymfonyMessageBusAdapter => $app->get(MessageBusContainer::class)->default(),
        );

        $app->set(
            SymfonyRoutableMessageBusAdapter::class,
            static fn(App $app): SymfonyRoutableMessageBusAdapter => new SymfonyRoutableMessageBusAdapter(
                $app->get(MessageBusContainer::class),
                $app->get(MessageBusContainer::class)->default(),
            ),
        );

        $app->set(
            ContainerTransportFactory::class,
            static fn(App $app): ContainerTransportFactory => new ContainerTransportFactory(
                $app->services,
                $app->get(MessageBusConfigStruct::class)->transport_factories,
            ),
        );

        $app->set(
            RedisTransportFactory::class,
            static fn(App $app): RedisTransportFactory => new RedisTransportFactory(
                $app->get(RedisManager::class),
                $app->environment,
            ),
        );

        $app->set(
            DoctrineTransportFactory::class,
            static fn(App $app): DoctrineTransportFactory => new DoctrineTransportFactory(
                $app->get(ConnectionProvider::class),
                new PhpSerializer(),
            ),
        );

        $app->set(
            SyncTransportFactory::class,
            static fn(App $app): SyncTransportFactory => new SyncTransportFactory(
                $app->get(MessageBusContainer::class),
            ),
        );

        $app->set(
            InMemoryTransportFactory::class,
            static fn(App $app): InMemoryTransportFactory => new InMemoryTransportFactory(
                $app->get(Clock::class),
            ),
        );

        $app->set(AmqpTransportFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(AmazonSqsTransportFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(
            SenderContainer::class,
            static fn(App $app): SenderContainer => new SenderContainer(\array_map(
                $app->services->get(TransportFactory::class)->make(...),
                $app->config->get('message_bus.senders') ?: [],
            )),
        );

        $app->set(
            ReceiverContainer::class,
            static fn(App $app): ReceiverContainer => new ReceiverContainer(\array_map(
                $app->services->get(TransportFactory::class)->make(...),
                $app->config->get('message_bus.receivers') ?: [],
            )),
        );

        $app->set(
            SendMessageMiddleware::class,
            static fn(App $app): SendMessageMiddleware => new SendMessageMiddleware(
                new SendersLocator(
                    $app->config->get('message_bus.routing') ?: [],
                    $app->services->get(SenderContainer::class),
                ),
                $app->get(EventDispatcherInterface::class),
            ),
        );

        $app->set(
            HandleMessageMiddleware::class,
            static fn(App $app): HandleMessageMiddleware => new HandleMessageMiddleware(new HandlersLocator(\array_map(
                static fn(array $handler_classes): array => \array_map($app->services->get(...), $handler_classes),
                $app->config->get('message_bus.handlers') ?: [],
            ))),
        );

        $app->set(
            DebugCommand::class,
            static fn(App $app): DebugCommand => new DebugCommand(
                $app->config->get('message_bus.handlers') ?: [],
            ),
        );

        $app->set(
            StatsCommand::class,
            static fn(App $app): StatsCommand => new StatsCommand(
                $app->get(ReceiverContainer::class),
                $app->get(ReceiverContainer::class)->keys(),
            ),
        );

        $app->set(
            ConsumeMessagesCommand::class,
            static fn(App $app): ConsumeMessagesCommand => ghost(fn(ConsumeMessagesCommand $ghost): null => $ghost->__construct(
                $app->get(RoutableMessageBus::class),
                $app->get(ReceiverContainer::class),
                $app->get(SymfonyEventDispatcherInterface::class),
                $app->get(LoggerInterface::class),
                $app->get(ReceiverContainer::class)->keys(),
                new ResetServicesListener(
                    $app->get(LongRunningProcessServiceResetter::class),
                    $app->get(LoggerInterface::class),
                ),
                $app->get(MessageBusContainer::class)->keys(),
            )),
        );

        $app->set(LongRunningProcessServiceResetter::class, NewInstanceServiceFactory::singleton());

        $app->set(
            StopWorkersCommand::class,
            static fn(App $app): StopWorkersCommand => new StopWorkersCommand($app->get(CacheItemPoolInterface::class)),
        );

        $app->set(AddErrorDetailsStampListener::class, NewInstanceServiceFactory::singleton());

        $app->set(DispatchPcntlSignalListener::class, NewInstanceServiceFactory::singleton());

        $app->set(
            SendFailedMessageForRetryListener::class,
            static fn(App $app): SendFailedMessageForRetryListener => new SendFailedMessageForRetryListener(
                $app->get(SenderContainer::class),
                new ImmutableObjectContainer(\array_map(
                    static function (array $strategy): RetryStrategyInterface {
                        if ($strategy['class'] === MultiplierRetryStrategy::class) {
                            return new MultiplierRetryStrategy(
                                $strategy['params']['max_retries'] ?? 3,
                                $strategy['params']['delay'] ?? 1000,
                                $strategy['params']['multiplier'] ?? 1,
                                $strategy['params']['max_delay_ms'] ?? 0,
                                $strategy['params']['jitter'] ?? 0.1,
                            );
                        }

                        throw new \InvalidArgumentException(
                            \sprintf('Retry Strategy "%s" Not Currently Supported', $strategy['class']),
                        );
                    },
                    $app->config->get('message_bus.retry_strategies') ?: [],
                )),
                $app->get(LoggerInterface::class),
                $app->get(EventDispatcherInterface::class),
            ),
        );

        $app->set(
            SendFailedMessageToFailureTransportListener::class,
            static function (App $app): SendFailedMessageToFailureTransportListener {
                return new SendFailedMessageToFailureTransportListener(
                    new SenderContainer(\array_map(
                        $app->services->get(TransportFactory::class)->make(...),
                        $app->config->get('message_bus.failure_senders') ?: [],
                    )),
                    $app->get(LoggerInterface::class),
                );
            },
        );

        $app->set(StopWorkerOnCustomStopExceptionListener::class, NewInstanceServiceFactory::singleton());

        $app->set(
            StopWorkerOnRestartSignalListener::class,
            static fn(App $app): StopWorkerOnRestartSignalListener => new StopWorkerOnRestartSignalListener(
                $app->get(CacheItemPoolInterface::class),
                $app->get(LoggerInterface::class),
            ),
        );

        $app->set(
            LogWorkerMessageFailedEvent::class,
            static fn(App $app): LogWorkerMessageFailedEvent => new LogWorkerMessageFailedEvent(
                $app->get(LoggerInterface::class),
            ),
        );

        $app->set(
            InvokableMessageHandler::class,
            static fn(App $app): InvokableMessageHandler => new InvokableMessageHandler(
                $app,
                $app->get(EventDispatcherInterface::class),
            ),
        );

        $app->set(
            RedispatchMessageHandler::class,
            ghost(static fn (RedispatchMessageHandler $ghost): null => $ghost->__construct(
                $app->get(MessageBusInterface::class),
            )),
        );
    }
}
