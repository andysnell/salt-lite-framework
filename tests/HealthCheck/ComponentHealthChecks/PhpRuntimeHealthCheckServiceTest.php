<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\HealthCheck\ComponentHealthChecks;

use Carbon\CarbonImmutable;
use PhoneBurner\SaltLite\Framework\App\Clock\StaticClock;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\PhpRuntimeHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhpRuntimeHealthCheckServiceTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $now = new CarbonImmutable();
        $clock = new StaticClock($now);
        $log_trace = LogTrace::make();

        $sut = new PhpRuntimeHealthCheckService($log_trace);

        self::assertEquals([new ComponentHealthCheck(
            component_name: 'php',
            component_type: 'component',
            status: HealthStatus::Pass,
            time: $now,
            additional: [
                'version' => \PHP_VERSION,
                'logTrace' => $log_trace,
            ],
        )], $sut($clock));
    }
}
