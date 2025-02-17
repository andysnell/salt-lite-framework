<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\ApplicationServiceProvider;

return [
    'container' => [
        'enable_deferred_service_registration' => true,
        'service_providers' => [
            // Application Service Providers
            ApplicationServiceProvider::class, // IMPORTANT: replace with application version
        ],
    ],
];
