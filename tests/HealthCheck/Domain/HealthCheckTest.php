<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\HealthCheck\Domain;

use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\ComponentHealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class HealthCheckTest extends TestCase
{
    #[Test]
    #[TestWith([HealthStatus::Pass, HealthStatus::Pass])]
    #[TestWith([HealthStatus::Warn, HealthStatus::Warn])]
    #[TestWith([HealthStatus::Fail, HealthStatus::Fail])]
    #[TestWith([null, HealthStatus::Pass])]
    public function happy_path_with_empty_case(HealthStatus|null $status, HealthStatus $expected): void
    {
        $health_check = new HealthCheck(status: $status);
        $this->assertSame($expected, $health_check->status);
        $this->assertSame([], $health_check->checks);
        $this->assertNull($health_check->version);
        $this->assertNull($health_check->release_id);
        $this->assertSame([], $health_check->notes);
        $this->assertNull($health_check->output);
        $this->assertSame([], $health_check->links);
        $this->assertNull($health_check->service_id);
        $this->assertNull($health_check->description);

        $this->assertSame([
            'status' => $expected,
        ], $health_check->jsonSerialize());
    }

    #[Test]
    public function happy_path_with_all_properties(): void
    {
        $component_check = new ComponentHealthCheck(
            component_name: 'x_component',
            measurement_name: 'x_measurement',
            component_id: 'x_component_id',
            component_type: 'x_component_type',
            observed_value: 'x_observed_value',
            observed_unit: 'x_observed_unit',
            status: HealthStatus::Pass,
            affected_endpoints: ['x_test_endpoint_1', 'x_test_endpoint_2'],
            time: new \DateTimeImmutable(),
            output: 'x_output',
            links: ['self' => '/test/x_component/healthz'],
            additional: ['additional_1' => 'test_additional_1', 'additional_2' => 'test_additional_2'],
        );

        $health_check = new HealthCheck(
            status: HealthStatus::Pass,
            version: 'x_version',
            release_id: 'x_release_id',
            notes: ['x_note_1', 'x_note_2'],
            output: 'x_output',
            checks: [$component_check],
            links: ['self' => '/test/x_component/healthz'],
            service_id: 'x_service_id',
            description: 'x_description',
        );

        $this->assertSame(HealthStatus::Pass, $health_check->status);
        $this->assertSame(['x_component:x_measurement' => [$component_check]], $health_check->checks);
        $this->assertSame('x_version', $health_check->version);
        $this->assertSame('x_release_id', $health_check->release_id);
        $this->assertSame(['x_note_1', 'x_note_2'], $health_check->notes);
        $this->assertSame('x_output', $health_check->output);
        $this->assertSame(['self' => '/test/x_component/healthz'], $health_check->links);
        $this->assertSame('x_service_id', $health_check->service_id);
        $this->assertSame('x_description', $health_check->description);

        $this->assertSame([
            'status' => HealthStatus::Pass,
            'version' => 'x_version',
            'releaseId' => 'x_release_id',
            'notes' => ['x_note_1', 'x_note_2'],
            'output' => 'x_output',
            'checks' => ['x_component:x_measurement' => [$component_check]],
            'links' => ['self' => '/test/x_component/healthz'],
            'serviceId' => 'x_service_id',
            'description' => 'x_description',
        ], $health_check->jsonSerialize());
    }

    #[Test]
    #[TestWith([HealthStatus::Pass, HealthStatus::Pass])]
    #[TestWith([HealthStatus::Warn, HealthStatus::Warn])]
    #[TestWith([HealthStatus::Fail, HealthStatus::Fail])]
    public function overall_status_can_be_derived_from_component_checks(
        HealthStatus $component_status,
        HealthStatus $expected_status,
    ): void {
        $component_check_1 = new ComponentHealthCheck(
            component_name: 'x_component',
            measurement_name: 'x_measurement',
            component_id: 'x_component_id',
            component_type: 'x_component_type',
            observed_value: 'x_observed_value',
            observed_unit: 'x_observed_unit',
            status: HealthStatus::Pass,
            affected_endpoints: ['x_test_endpoint_1', 'x_test_endpoint_2'],
            time: new \DateTimeImmutable(),
            output: 'x_output',
            links: ['self' => '/test/x_component/healthz'],
            additional: ['additional_1' => 'test_additional_1', 'additional_2' => 'test_additional_2'],
        );

        $component_check_2 = new ComponentHealthCheck(
            component_name: 'x_component',
            measurement_name: 'x_measurement',
            component_id: 'x_component_id',
            component_type: 'x_component_type',
            observed_value: 'x_observed_value',
            observed_unit: 'x_observed_unit',
            status: $component_status,
            affected_endpoints: ['x_test_endpoint_1', 'x_test_endpoint_2'],
            time: new \DateTimeImmutable(),
            output: 'x_output',
            links: ['self' => '/test/x_component/healthz'],
            additional: ['additional_1' => 'test_additional_1', 'additional_2' => 'test_additional_2'],
        );

        $component_check_3 = new ComponentHealthCheck(
            component_name: 'z_component',
            component_id: 'x_component_id',
            component_type: 'x_component_type',
            observed_value: 'x_observed_value',
            observed_unit: 'x_observed_unit',
            status: HealthStatus::Pass,
            affected_endpoints: ['x_test_endpoint_1', 'x_test_endpoint_2'],
            time: new \DateTimeImmutable(),
            output: 'x_output',
            links: ['self' => '/test/x_component/healthz'],
            additional: ['additional_1' => 'test_additional_1', 'additional_2' => 'test_additional_2'],
        );

        $health_check = new HealthCheck(
            version: 'x_version',
            release_id: 'x_release_id',
            notes: ['x_note_1', 'x_note_2'],
            output: 'x_output',
            checks: [$component_check_1, $component_check_2, $component_check_3],
            links: ['self' => '/test/x_component/healthz'],
            service_id: 'x_service_id',
            description: 'x_description',
        );

        $this->assertSame($expected_status, $health_check->status);
        $this->assertSame([
            'x_component:x_measurement' => [
                $component_check_1,
                $component_check_2,
            ],
            'z_component' => [
            $component_check_3,
            ],
        ], $health_check->checks);
        $this->assertSame('x_version', $health_check->version);
        $this->assertSame('x_release_id', $health_check->release_id);
        $this->assertSame(['x_note_1', 'x_note_2'], $health_check->notes);
        $this->assertSame('x_output', $health_check->output);
        $this->assertSame(['self' => '/test/x_component/healthz'], $health_check->links);
        $this->assertSame('x_service_id', $health_check->service_id);
        $this->assertSame('x_description', $health_check->description);

        $this->assertSame([
            'status' => $expected_status,
            'version' => 'x_version',
            'releaseId' => 'x_release_id',
            'notes' => ['x_note_1', 'x_note_2'],
            'output' => 'x_output',
            'checks' => [
                'x_component:x_measurement' => [
                    $component_check_1,
                    $component_check_2,
                ],
                'z_component' => [
                    $component_check_3,
                ],
            ],
            'links' => ['self' => '/test/x_component/healthz'],
            'serviceId' => 'x_service_id',
            'description' => 'x_description',
        ], $health_check->jsonSerialize());
    }
}
