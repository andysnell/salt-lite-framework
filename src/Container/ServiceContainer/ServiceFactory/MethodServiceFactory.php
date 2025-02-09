<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;
use Psr\Container\ContainerInterface;

final readonly class MethodServiceFactory implements ServiceFactory
{
    /**
     * @param class-string|object $class_or_object
     */
    public function __construct(
        private object|string $class_or_object,
        private string $method = 'make',
    ) {
    }

    public function __invoke(ContainerInterface $app): object
    {
        $object = \is_string($this->class_or_object) ? $app->get($this->class_or_object) : $this->class_or_object;

        return $object->{$this->method}($app);
    }
}
