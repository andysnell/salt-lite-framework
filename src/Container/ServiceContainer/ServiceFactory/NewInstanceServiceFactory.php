<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

/**
 * Instantiates a new object of the given class, passing the given arguments to
 * the constructor. If the class is not provided in the constructor, we'll use
 * the entry id of the service being resolved by the container.
 */
final readonly class NewInstanceServiceFactory implements ServiceFactory
{
    public function __construct(
        private string|null $class = null,
        private array $args = [],
    ) {
    }

    public function __invoke(App $app, string $id): object
    {
        return new ($this->class ?? $id)(...$this->args);
    }
}
