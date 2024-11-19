<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\HealthCheck\ComponentHealthChecks;

use Carbon\CarbonImmutable;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\RedisHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Clock\StaticClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class RedisHealthCheckServiceTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $now = new CarbonImmutable();
        $clock = new StaticClock($now);
        $log_trace = LogTrace::make();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $redis = $this->createMock(\Redis::class);
        $redis->expects($this->once())
            ->method('client')
            ->with('list')
            ->willReturn(['client1', 'client2', 'client3']);

        $sut = new RedisHealthCheckService($redis, $log_trace, $logger);

        $response = $sut($clock);

        self::assertEquals([
            new ComponentHealthCheck(
                component_name: 'redis',
                measurement_name: 'connections',
                component_type: 'datastore',
                observed_value: 3,
                status: HealthStatus::Pass,
                time: $now,
            ),
            new ComponentHealthCheck(
                component_name: 'redis',
                measurement_name: 'responseTime',
                component_type: 'datastore',
                observed_value: $response[1]->observed_value,
                observed_unit: 'ms',
                status: HealthStatus::Pass,
                time: $now,
            ),
        ], $response);

        self::assertIsFloat($response[1]->observed_value);
    }

    #[Test]
    public function sad_path_catches_exceptions(): void
    {
        $exception = new \RuntimeException('test exception');

        $now = new CarbonImmutable();
        $clock = new StaticClock($now);
        $log_trace = LogTrace::make();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Health Check Failure: {component}', [
                'component' => 'redis',
                'exception' => $exception,
            ]);

        $redis = $this->createMock(\Redis::class);
        $redis->expects($this->once())
            ->method('client')
            ->with('list')
            ->willThrowException($exception);

        $sut = new RedisHealthCheckService($redis, $log_trace, $logger);

        $response = $sut($clock);

        self::assertEquals([
            new ComponentHealthCheck(
                component_name: 'redis',
                component_type: 'datastore',
                status: HealthStatus::Fail,
                time: $now,
                output: 'Health Check Failed (Log Trace: ' . $log_trace . ')',
            ),
        ], $response);
    }
}
