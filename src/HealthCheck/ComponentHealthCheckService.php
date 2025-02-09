<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck;

use PhoneBurner\SaltLite\Framework\App\Clock\Clock;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;

interface ComponentHealthCheckService
{
    /**
     * @return array<ComponentHealthCheck>
     */
    public function __invoke(Clock $clock): array;
}
