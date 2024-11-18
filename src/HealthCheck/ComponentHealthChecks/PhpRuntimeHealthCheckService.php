<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks;

use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentType;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Clock\Clock;

class PhpRuntimeHealthCheckService implements ComponentHealthCheckService
{
    public const string COMPONENT_NAME = 'php';

    public function __construct(
        private readonly LogTrace $log_trace,
    ) {
    }

    #[\Override]
    public function __invoke(Clock $clock): array
    {
        return [
            new ComponentHealthCheck(
                component_name: self::COMPONENT_NAME,
                component_type: ComponentType::COMPONENT,
                status: HealthStatus::Pass,
                time: $clock->now(),
                additional: [
                    'version' => \PHP_VERSION,
                    'logTrace' => $this->log_trace,
                ],
            ),
        ];
    }
}
