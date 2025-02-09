<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Scheduler;

use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;
use Traversable;

/**
 * @implements \IteratorAggregate<string, ScheduleProviderInterface>
 * @implements ServiceProviderInterface<ScheduleProviderInterface>
 */
final readonly class ScheduleProviderCollection implements ServiceProviderInterface, \IteratorAggregate
{
    public function __construct(
        private array $schedule_providers = [],
    ) {
    }

    #[\Override]
    public function get(string $id): mixed
    {
        return $this->schedule_providers[$id] ?? null;
    }

    #[\Override]
    public function has(string $id): bool
    {
        return isset($this->schedule_providers[$id]);
    }

    #[\Override]
    public function getProvidedServices(): array
    {
        return $this->schedule_providers;
    }

    public function getIterator(): Traversable
    {
        yield from $this->schedule_providers;
    }
}
