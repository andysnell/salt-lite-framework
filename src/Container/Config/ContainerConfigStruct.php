<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\ApplicationServiceProvider;

final readonly class ContainerConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param list<class-string<ServiceProvider>> $service_providers
     */
    public function __construct(
        public bool $enable_deferred_service_registration = false,
        public array $service_providers = [ApplicationServiceProvider::class],
    ) {
    }
}
