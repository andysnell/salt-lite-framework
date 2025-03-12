<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console;

use PhoneBurner\SaltLite\App\Event\KernelExecutionComplete;
use PhoneBurner\SaltLite\App\Event\KernelExecutionStart;
use PhoneBurner\SaltLite\App\Kernel;
use Psr\EventDispatcher\EventDispatcherInterface;

class CliKernel implements Kernel
{
    public function __construct(
        private readonly ConsoleApplication $application,
        private readonly EventDispatcherInterface $event_dispatcher,
    ) {
    }

    #[\Override]
    public function run(): never
    {
        $this->event_dispatcher->dispatch(new KernelExecutionStart($this));
        try {
            $exit_code = $this->application->run();
        } finally {
            $this->event_dispatcher->dispatch(new KernelExecutionComplete($this));
        }

        /** @phpstan-ignore disallowed.exit */
        exit($exit_code);
    }
}
