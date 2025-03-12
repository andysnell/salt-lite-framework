<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework;

use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\ApplicationRouteProvider;

/**
 * @codeCoverageIgnore
 */
class ApplicationServiceProvider implements ServiceProvider
{
    public static function bind(): array
    {
        return [];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            ApplicationRouteProvider::class,
            static fn(App $app): ApplicationRouteProvider => new ApplicationRouteProvider(),
        );
    }
}
