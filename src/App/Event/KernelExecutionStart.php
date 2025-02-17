<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Event;

use PhoneBurner\SaltLite\Framework\App\Kernel;

class KernelExecutionStart
{
    public function __construct(public readonly Kernel $kernel)
    {
    }
}
