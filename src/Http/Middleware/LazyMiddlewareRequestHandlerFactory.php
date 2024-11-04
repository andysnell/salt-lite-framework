<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Middleware\Exception\InvalidMiddlewareConfiguration;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Given an array of MiddlewareInterface instances, or MiddlewareInterface class
 * name strings, this class will produce RequestHandler structures composed of the
 * passed object instances or LazyMiddleware instances that wrap the resolved
 * middleware classes.
 */
class LazyMiddlewareRequestHandlerFactory implements MiddlewareRequestHandlerFactory
{
    public function __construct(protected readonly ContainerInterface $container)
    {
    }

    #[\Override]
    public function queue(
        RequestHandlerInterface $fallback_handler,
        iterable $middleware_chain = [],
    ): MiddlewareQueue {
        $middleware_handler = MiddlewareQueue::make($fallback_handler);
        foreach ($middleware_chain as $middleware) {
            $this->resolve($middleware_handler, $middleware);
        }

        return $middleware_handler;
    }

    #[\Override]
    public function stack(
        RequestHandlerInterface $fallback_handler,
        iterable $middleware_chain = [],
    ): MiddlewareStack {
        $middleware_handler = MiddlewareStack::make($fallback_handler);
        foreach ($middleware_chain as $middleware) {
            $this->resolve($middleware_handler, $middleware);
        }

        return $middleware_handler;
    }

    protected function resolve(
        MutableMiddlewareRequestHandler $handler,
        MiddlewareInterface|string $middleware,
    ): MutableMiddlewareRequestHandler {
        return match (true) {
            $middleware instanceof MiddlewareInterface => $handler->push($middleware),
            \is_a($middleware, MiddlewareInterface::class, true) => $this->pushMiddlewareClass($handler, $middleware),
            default => throw new InvalidMiddlewareConfiguration(ErrorMessage::RESOLUTION_ERROR),
        };
    }

    protected function pushMiddlewareClass(
        MutableMiddlewareRequestHandler $handler,
        string $middleware_class,
    ): MutableMiddlewareRequestHandler {
        return $handler->push(LazyMiddleware::make($this->container, $middleware_class));
    }
}
