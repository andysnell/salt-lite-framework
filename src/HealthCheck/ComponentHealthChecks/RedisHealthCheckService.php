<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks;

use PhoneBurner\SaltLite\Framework\Domain\Time\StopWatch;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentType;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\MeasurementName;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Clock\Clock;
use Psr\Log\LoggerInterface;

class RedisHealthCheckService implements ComponentHealthCheckService
{
    public const string COMPONENT_NAME = 'redis';

    public function __construct(
        private readonly \Redis $redis,
        private readonly LogTrace $log_trace,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(Clock $clock): array
    {
        $now = $clock->now();
        try {
            $timer = StopWatch::start();
            $connections = \count($this->redis->client('list'));
            $elapsed = $timer->elapsed();

            return [
                new ComponentHealthCheck(
                    component_name: self::COMPONENT_NAME,
                    measurement_name: MeasurementName::CONNECTIONS,
                    component_type: ComponentType::DATASTORE,
                    observed_value: $connections,
                    status: HealthStatus::Pass,
                    time: $now,
                ),
                new ComponentHealthCheck(
                    component_name: self::COMPONENT_NAME,
                    measurement_name: MeasurementName::RESPONSE_TIME,
                    component_type: ComponentType::DATASTORE,
                    observed_value: $elapsed->inMilliseconds(),
                    observed_unit: 'ms',
                    status: HealthStatus::Pass,
                    time: $now,
                ),
            ];
        } catch (\Throwable $e) {
            $this->logger->error('Health Check Failure: {component}', [
                'component' => self::COMPONENT_NAME,
                'exception' => $e,
            ]);

            return [
                new ComponentHealthCheck(
                    component_name: self::COMPONENT_NAME,
                    component_type: ComponentType::DATASTORE,
                    status: HealthStatus::Fail,
                    time: $now,
                    output: 'Health Check Failed (Log Trace: ' . $this->log_trace . ')',
                ),
            ];
        }
    }
}
