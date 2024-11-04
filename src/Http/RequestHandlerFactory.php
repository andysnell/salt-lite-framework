<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This class is intended to give us some extra assurance that we are resolving
 * a `RequestHandlerInterface` instance, and the flexibility that if some other
 * middleware in the chain has attached an instantiated object, that we will just
 * use that.
 */
class RequestHandlerFactory
{
    public const string TYPE_ERROR = 'Value Must Be RequestHandlerInterface Instance or Class Name String';

    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function make(RequestHandlerInterface|string $request_handler): RequestHandlerInterface
    {
        return match (true) {
            $request_handler instanceof RequestHandlerInterface => $request_handler,
            \is_a($request_handler, RequestHandlerInterface::class, true) => $this->container->get($request_handler),
            default => throw new \InvalidArgumentException(self::TYPE_ERROR),
        };
    }
}
