<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\ApplicationServiceProvider;
use PhoneBurner\SaltLite\Framework\Container\Config\ContainerConfigStruct;

return [
    'container' => new ContainerConfigStruct(
        enable_deferred_service_registration: true,
        service_providers: [
            // Application Service Providers
            ApplicationServiceProvider::class, // IMPORTANT: replace this with the application version
        ],
    ),
];
