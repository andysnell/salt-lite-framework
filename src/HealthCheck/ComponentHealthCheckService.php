<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck;

use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;
use PhoneBurner\SaltLite\Framework\Util\Clock\Clock;

interface ComponentHealthCheckService
{
    /**
     * @return array<ComponentHealthCheck>
     */
    public function __invoke(Clock $clock): array;
}
