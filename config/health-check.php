<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\MySqlHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\PhpRuntimeHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\RedisHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\Config\HealthCheckConfigStruct;

return [
    'health_check' => new HealthCheckConfigStruct(
        services: [
            PhpRuntimeHealthCheckService::class,
            MySqlHealthCheckService::class,
            RedisHealthCheckService::class,
        ],
    ),
];
