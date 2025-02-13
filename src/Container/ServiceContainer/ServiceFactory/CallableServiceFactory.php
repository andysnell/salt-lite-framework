<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

final readonly class CallableServiceFactory implements ServiceFactory
{
    public function __construct(private \Closure $closure)
    {
    }

    public function __invoke(App $app, string $id): object
    {
        return ($this->closure)($app);
    }
}
