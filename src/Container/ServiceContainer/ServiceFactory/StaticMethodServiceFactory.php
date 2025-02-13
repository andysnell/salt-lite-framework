<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

final readonly class StaticMethodServiceFactory implements ServiceFactory
{
    /**
     * @param class-string $class
     */
    public function __construct(
        private string $class,
        private string $method = 'make',
    ) {
    }

    public function __invoke(App $app, string $id): object
    {
        return $this->class::{$this->method}($app);
    }
}
