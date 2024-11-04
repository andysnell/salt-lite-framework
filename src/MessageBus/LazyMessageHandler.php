<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use Psr\Container\ContainerInterface;

class LazyMessageHandler
{
    public function __construct(
        public readonly ContainerInterface $container,
        public readonly string $handler_class,
    ) {
    }

    public function __invoke(object $message): void
    {
        $handler = $this->container->get($this->handler_class);
        \assert(\is_callable($handler));
        $handler($message);
    }
}
