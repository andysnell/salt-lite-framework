<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Scheduler;

use Crell\AttributeUtils\ClassAnalyzer;
use PhoneBurner\SaltLite\Framework\Cache\CacheKey;
use PhoneBurner\SaltLite\Framework\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Framework\Cache\Lock\SymfonyLockAdapter;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\MessageBus\LazyMessageHandler;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Symfony\Component\Clock\Clock as SymfonyClock;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\Command\DebugCommand;
use Symfony\Component\Scheduler\EventListener\DispatchSchedulerEventListener;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Component\Scheduler\Scheduler;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class SchedulerServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {

        $container->set(Scheduler::class, static function (ContainerInterface $container): Scheduler {

            return new Scheduler(
                \array_map(
                    static fn (array $handler_classes): array => \array_map(
                        static fn (string $handler_class): LazyMessageHandler => new LazyMessageHandler($container, $handler_class),
                        $handler_classes,
                    ),
                    $container->get(Configuration::class)->get('bus.handlers') ?: [],
                ),
                $container->get(ScheduleCollection::class)->getProvidedServices(),
                new SymfonyClock($container->get(ClockInterface::class)),
                $container->get(EventDispatcherInterface::class),
            );
        });

        $container->set(ScheduleCollection::class, static function (ContainerInterface $container): ScheduleCollection {
            $class_analyzer = $container->get(ClassAnalyzer::class);
            $default_attribute = new AsSchedule();
            $schedules = [];

            $cache = new ProxyAdapter($container->get(CacheItemPoolInterface::class), 'scheduler');

            foreach ($container->get(Configuration::class)->get('scheduler.schedule_providers') ?: [] as $provider) {
                \assert(\is_string($provider) && \is_a($provider, ScheduleProviderInterface::class, true));
                $name = $class_analyzer->analyze($provider, AsSchedule::class)->name;
                if ($name === $default_attribute->name) {
                    $name = $provider;
                }

                $key = CacheKey::make('scheduler', $name);
                $lock = $container->get(LockFactory::class)->make($key, Ttl::seconds(60));
                \assert($lock instanceof SymfonyLockAdapter);

                $schedule = $container->get($provider)->getSchedule();
                \assert($schedule instanceof Schedule);
                $schedule->lock($lock->wrapped());
                $schedule->stateful($cache);
                $schedules[$name] = $schedule;
            }

            return new ScheduleCollection($schedules);
        });

        $container->set(DebugCommand::class, static function (ContainerInterface $container): DebugCommand {
            return new DebugCommand($container->get(ScheduleCollection::class));
        });

        $container->set(
            DispatchSchedulerEventListener::class,
            static function (ContainerInterface $container): DispatchSchedulerEventListener {
                return new DispatchSchedulerEventListener(
                    $container,
                    $container->get(EventDispatcherInterface::class),
                );
            },
        );
    }
}
