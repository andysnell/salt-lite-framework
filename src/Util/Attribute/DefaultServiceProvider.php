<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Attribute;

use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DefaultServiceProvider
{
    /**
     * @param class-string<ServiceProvider>|null $service_provider
     */
    public function __construct(
        public string|null $service_provider = null,
    ) {
    }
}
