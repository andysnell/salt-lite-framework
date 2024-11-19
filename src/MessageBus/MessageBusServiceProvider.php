<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Scheduler\ScheduleCollection;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\Connection as AmqpConnection;
use Symfony\Component\Messenger\Bridge\Redis\Transport\Connection as RedisConnection;
use Symfony\Component\Messenger\Bridge\Redis\Transport\RedisTransport;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\EventListener\AddErrorDetailsStampListener;
use Symfony\Component\Messenger\EventListener\DispatchPcntlSignalListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnCustomStopExceptionListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnFailureLimitListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMemoryLimitListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnTimeLimitListener;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Scheduler\Generator\MessageGenerator;
use Symfony\Component\Scheduler\Messenger\SchedulerTransport;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class MessageBusServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->bind(MessageBusInterface::class, SymfonyMessageBusAdapter::class);
        $container->bind(MessageBus::class, SymfonyMessageBusAdapter::class);
        $container->set(
            SymfonyMessageBusAdapter::class,
            static function (ContainerInterface $container): SymfonyMessageBusAdapter {
                $config = $container->get(Configuration::class);
                return new SymfonyMessageBusAdapter([
                    new SendMessageMiddleware(
                        new SendersLocator($config->get('bus.senders') ?: [], $container),
                        $container->get(EventDispatcherInterface::class),
                    ),
                    new HandleMessageMiddleware(new HandlersLocator(\array_map(
                        static fn (array $handler_classes): array => \array_map(
                            static fn (string $handler_class): LazyMessageHandler => new LazyMessageHandler($container, $handler_class),
                            $handler_classes,
                        ),
                        $config->get('bus.handlers') ?: [],
                    ))),
                ]);
            },
        );

        $container->set(
            RoutableMessageBus::class,
            static function (ContainerInterface $container): RoutableMessageBus {
                return new RoutableMessageBus($container, $container->get(MessageBusInterface::class));
            },
        );

        $container->set(
            TransportInterface::class,
            static function (ContainerInterface $container): TransportInterface {
                $transport_class = $container->get(Configuration::class)->get('bus.transport') ?? SyncTransport::class;
                return $container->get($transport_class);
            },
        );

        $container->set(
            SyncTransport::class,
            static function (ContainerInterface $container): SyncTransport {
                return new SyncTransport($container->get(MessageBusInterface::class));
            },
        );

        $container->set(
            AmqpTransport::class,
            static function (ContainerInterface $container): AmqpTransport {
                return new AmqpTransport(AmqpConnection::fromDsn('amqp://user:password@rabbitmq:5672/%2f'));
            },
        );

        $container->set(
            RedisTransport::class,
            static function (ContainerInterface $container): RedisTransport {
                return new RedisTransport(new RedisConnection([
                    'group' => Str::kabob($container->get(Configuration::class)->get('app.name') ?? 'salt-lite'),
                ], $container->get(\Redis::class)));
            },
        );

        $container->set(
            SchedulerTransport::class,
            static function (ContainerInterface $container): SchedulerTransport {
                return new SchedulerTransport(
                    new MessageGenerator(
                        $container->get(ScheduleCollection::class)->get('example'),
                        'example',
                        $container->get(ClockInterface::class),
                    ),
                );
            },
        );

        $container->set(
            ConsumeMessagesCommand::class,
            static function (ContainerInterface $container): ConsumeMessagesCommand {
                return new ConsumeMessagesCommand(
                    $container->get(RoutableMessageBus::class),
                    $container,
                    $container->get(SymfonyEventDispatcher::class),
                    $container->get(LoggerInterface::class),
                    $container->get(Configuration::class)->get('bus.receivers') ?? [],
                );
            },
        );

        $container->set(
            AddErrorDetailsStampListener::class,
            static function (ContainerInterface $container): AddErrorDetailsStampListener {
                return new AddErrorDetailsStampListener();
            },
        );

        $container->set(
            DispatchPcntlSignalListener::class,
            static function (ContainerInterface $container): DispatchPcntlSignalListener {
                return new DispatchPcntlSignalListener();
            },
        );

//        $container->set(
//            SendFailedMessageForRetryListener::class,
//            static function (ContainerInterface $container): SendFailedMessageForRetryListener {
//                return new SendFailedMessageForRetryListener();
//            },
//        );
//
//        $container->set(
//            SendFailedMessageToFailureTransportListener::class,
//            static function (ContainerInterface $container): SendFailedMessageToFailureTransportListener {
//                return new SendFailedMessageToFailureTransportListener();
//            },
//        );

        $container->set(
            StopWorkerOnCustomStopExceptionListener::class,
            static function (ContainerInterface $container): StopWorkerOnCustomStopExceptionListener {
                return new StopWorkerOnCustomStopExceptionListener();
            },
        );

        $container->set(
            StopWorkerOnFailureLimitListener::class,
            static function (ContainerInterface $container): StopWorkerOnFailureLimitListener {
                return new StopWorkerOnFailureLimitListener(
                    $container->get(Configuration::class)->get('bus.worker.max_failures'),
                    $container->get(LoggerInterface::class),
                );
            },
        );

        $container->set(
            StopWorkerOnMemoryLimitListener::class,
            static function (ContainerInterface $container): StopWorkerOnMemoryLimitListener {
                return new StopWorkerOnMemoryLimitListener(
                    $container->get(Configuration::class)->get('bus.worker.max_memory_usage_bytes'),
                    $container->get(LoggerInterface::class),
                );
            },
        );

        $container->set(
            StopWorkerOnMessageLimitListener::class,
            static function (ContainerInterface $container): StopWorkerOnMessageLimitListener {
                return new StopWorkerOnMessageLimitListener(
                    $container->get(Configuration::class)->get('bus.worker.max_messages'),
                    $container->get(LoggerInterface::class),
                );
            },
        );

        $container->set(
            StopWorkerOnRestartSignalListener::class,
            static function (ContainerInterface $container): StopWorkerOnRestartSignalListener {
                return new StopWorkerOnRestartSignalListener(
                    $container->get(CacheItemPoolInterface::class),
                    $container->get(LoggerInterface::class),
                );
            },
        );

        $container->set(
            StopWorkerOnTimeLimitListener::class,
            static function (ContainerInterface $container): StopWorkerOnTimeLimitListener {
                return new StopWorkerOnTimeLimitListener(
                    $container->get(Configuration::class)->get('bus.worker.time_limit_seconds'),
                    $container->get(LoggerInterface::class),
                );
            },
        );
    }
}
