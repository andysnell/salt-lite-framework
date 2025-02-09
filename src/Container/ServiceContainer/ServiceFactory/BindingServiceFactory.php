<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;

use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;
use Psr\Container\ContainerInterface;

/**
 * Factory class for binding an id (e.g. interface) to an entry in the container
 *(entry_id).
 */
final readonly class BindingServiceFactory implements ServiceFactory
{
    public function __construct(public string $entry_id)
    {
    }

    public function __invoke(ContainerInterface $app): object
    {
        return $app->get($this->entry_id);
    }
}
