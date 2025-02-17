<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Attribute;

use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\ClassString;

/**
 * @implements MapsToClassString<ServiceProvider>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DefaultServiceProvider implements MapsToClassString
{
    /**
     * @param class-string<ServiceProvider>|null $service_provider
     */
    public function __construct(
        public string|null $service_provider = null,
    ) {
    }

    public function mapsTo(): ClassString
    {
        /**
         * @var ClassString<ServiceProvider> $class_string
         */
        $class_string = new ClassString($this->service_provider ?? throw new \LogicException(
            'No Service Provider Mapped',
        ));

        return $class_string;
    }
}
