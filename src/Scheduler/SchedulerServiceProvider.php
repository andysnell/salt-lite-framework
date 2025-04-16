<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Scheduler;

use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Configuration\Exception\InvalidConfiguration;
use PhoneBurner\SaltLite\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\MessageBus\Container\ReceiverContainer;
use PhoneBurner\SaltLite\Framework\Scheduler\Command\ConsumeScheduledMessagesCommand;
use PhoneBurner\SaltLite\Type\Type;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Scheduler\Command\DebugCommand;
use Symfony\Component\Scheduler\EventListener\DispatchSchedulerEventListener;
use Symfony\Component\Scheduler\Generator\MessageGenerator;
use Symfony\Component\Scheduler\Messenger\SchedulerTransport;

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
            $schedule_providers = [];
            foreach ($app->config->get('scheduler.schedule_providers') ?: [] as $schedule_provider_class) {
                \assert(Type::isClassStringOf(ScheduleProvider::class, $schedule_provider_class));
                $name = $schedule_provider_class::getName();
                if (\array_key_exists($name, $schedule_providers)) {
                    throw new InvalidConfiguration('Duplicate schedule provider name: ' . $name);
                }

                $schedule_providers[$name] = $app->get($schedule_provider_class);
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
                foreach ($app->get(ScheduleProviderCollection::class) as $schedule_provider) {
                    \assert($schedule_provider instanceof ScheduleProvider);
                    $receiver_locator->set('schedule_' . $schedule_provider::getName(), new SchedulerTransport(
                        new MessageGenerator($schedule_provider, $schedule_provider::getName(), $clock),
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
