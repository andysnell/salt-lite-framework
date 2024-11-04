<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Scheduler;

use Symfony\Component\Scheduler\Schedule;
use Symfony\Contracts\Service\ServiceProviderInterface;

/** @implements ServiceProviderInterface<Schedule> */
class ScheduleCollection implements ServiceProviderInterface
{
    public function __construct(
        private readonly array $schedules = [],
    ) {
    }

    #[\Override]
    public function get(string $id): mixed
    {
        return $this->schedules[$id] ?? null;
    }

    #[\Override]
    public function has(string $id): bool
    {
        return isset($this->schedules[$id]);
    }

    #[\Override]
    public function getProvidedServices(): array
    {
        return $this->schedules;
    }
}
