<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks;

use Doctrine\DBAL\Connection;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentType;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\MeasurementName;
use PhoneBurner\SaltLite\Logging\LogTrace;
use PhoneBurner\SaltLite\Time\Clock\Clock;
use PhoneBurner\SaltLite\Time\StopWatch;
use Psr\Log\LoggerInterface;

class MySqlHealthCheckService implements ComponentHealthCheckService
{
    public const string COMPONENT_NAME = 'mysql';

    public const string SQL = <<<SQL
        SELECT COUNT(`host`) AS connections FROM information_schema.processlist;
        SQL;

    public function __construct(
        private readonly Connection $connection,
        private readonly LogTrace $log_trace,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public function __invoke(Clock $clock): array
    {
        $now = $clock->now();
        try {
            $timer = StopWatch::start();
            $connections = (int)$this->connection->fetchOne(self::SQL);
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
