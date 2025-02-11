<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

final readonly class NewInstanceServiceFactory implements ServiceFactory
{
    public function __construct(
        private string $class,
        private array $args = [],
    ) {
    }

    public function __invoke(App $app): object
    {
        return new $this->class(...$this->args);
    }
}
