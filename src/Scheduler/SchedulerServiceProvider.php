<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Scheduler;

use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Cache\CacheKey;
use PhoneBurner\SaltLite\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Cache\Lock\SymfonyLockAdapter;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\ReceiverContainer;
use PhoneBurner\SaltLite\Framework\Scheduler\Command\ConsumeScheduledMessagesCommand;
use PhoneBurner\SaltLite\Time\Ttl;
use PhoneBurner\SaltLite\Type\Type;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Scheduler\Command\DebugCommand;
use Symfony\Component\Scheduler\EventListener\DispatchSchedulerEventListener;
use Symfony\Component\Scheduler\Generator\MessageGenerator;
use Symfony\Component\Scheduler\Messenger\SchedulerTransport;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class SchedulerServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            ScheduleProviderCollection::class,
            DebugCommand::class,
            DispatchSchedulerEventListener::class,
            ConsumeScheduledMessagesCommand::class,
        ];
    }

    public static function bind(): array
    {
        return [];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(ScheduleProviderCollection::class, static function (App $app): ScheduleProviderCollection {
            $cache = $app->get(CacheItemPoolInterface::class);

            $schedule_providers = [];
            foreach ($app->config->get('scheduler.schedule_providers') ?: [] as $name => $class) {
                $key = CacheKey::make('scheduler', $name);
                $lock = Type::of(SymfonyLockAdapter::class, $app->get(LockFactory::class)
                    ->make($key, Ttl::seconds(60)))
                    ->wrapped();

                $schedule_provider = Type::of(ScheduleProviderInterface::class, $app->get($class));
                $schedule_provider->getSchedule()
                    ->lock($lock)
                    ->stateful(new ProxyAdapter($cache, (string)$key));

                $schedule_providers[$name] = $schedule_provider;
            }

            return new ScheduleProviderCollection($schedule_providers);
        });

        $app->set(
            DebugCommand::class,
            static fn(App $app): DebugCommand => new DebugCommand(
                $app->get(ScheduleProviderCollection::class),
            ),
        );

        $app->set(
            ConsumeScheduledMessagesCommand::class,
            static function (App $app): ConsumeScheduledMessagesCommand {
                $clock = $app->get(ClockInterface::class);

                // Add a transport instance for every configured schedule provider
                $receiver_locator = new ReceiverContainer();
                foreach ($app->get(ScheduleProviderCollection::class) as $name => $schedule_provider) {
                    $receiver_locator->set('schedule_' . $name, new SchedulerTransport(
                        new MessageGenerator($schedule_provider, $name, $clock),
                    ));
                }

                return new ConsumeScheduledMessagesCommand(
                    $app->get(RoutableMessageBus::class),
                    $receiver_locator,
                    $app->get(SymfonyEventDispatcherInterface::class),
                    $app->get(LoggerInterface::class),
                    $receiver_locator->keys(),
                );
            },
        );

        $app->set(
            DispatchSchedulerEventListener::class,
            static fn(App $app): DispatchSchedulerEventListener => new DispatchSchedulerEventListener(
                $app->get(ScheduleProviderCollection::class),
                $app->get(EventDispatcherInterface::class),
            ),
        );
    }
}
