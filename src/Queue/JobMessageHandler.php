<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Queue;

use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Queue\Event\JobHandlingComplete;
use PhoneBurner\SaltLite\Framework\Queue\Event\JobHandlingFailed;
use PhoneBurner\SaltLite\Framework\Queue\Event\JobHandlingStart;
use Psr\EventDispatcher\EventDispatcherInterface;

class JobMessageHandler
{
    public function __construct(
        private readonly MutableContainer $container,
        private readonly EventDispatcherInterface $event_dispatcher,
    ) {
    }

    public function __invoke(Job $job): void
    {
        $this->event_dispatcher->dispatch(new JobHandlingStart($job));
        try {
            $this->container->call($job, '__invoke');
            $this->event_dispatcher->dispatch(new JobHandlingComplete($job));
        } catch (\Throwable $e) {
            $this->event_dispatcher->dispatch(new JobHandlingFailed($job, $e));
            throw $e;
        }
    }
}
