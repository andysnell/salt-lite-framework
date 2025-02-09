<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\MySqlHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\PhpRuntimeHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\RedisHealthCheckService;

return [
    'health_check' => [
        'services' => [
            PhpRuntimeHealthCheckService::class,
            MySqlHealthCheckService::class,
            RedisHealthCheckService::class,
        ],
    ],
];
