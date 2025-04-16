<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthCheckService;

final readonly class HealthCheckConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param list<class-string<ComponentHealthCheckService>> $services
     */
    public function __construct(public array $services = [])
    {
    }
}
