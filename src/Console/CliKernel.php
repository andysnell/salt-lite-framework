<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console;

use PhoneBurner\SaltLite\Framework\App\Kernel;

class CliKernel implements Kernel
{
    public function __construct(private readonly ConsoleApplication $application)
    {
    }

    #[\Override]
    public function run(): never
    {
        /** @phpstan-ignore disallowed.exit */
        exit($this->application->run());
    }
}
