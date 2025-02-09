<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Middleware\Exception\InvalidMiddlewareConfiguration;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Given an array of MiddlewareInterface instances, or MiddlewareInterface class
 * name strings, this class will produce RequestHandler structures resolved from
 * the PSR-11 container. If the container happens to be a `\PhoneBurner\SaltLite\Framework\Container\LazyContainer`, then
 * the middleware will be resolved lazily.
 */
class LazyMiddlewareRequestHandlerFactory implements MiddlewareRequestHandlerFactory
{
    private readonly \Closure $proxy_factory;

    public function __construct(private readonly ContainerInterface $container)
    {
        // Note that since the Lazy Proxy factory cannot return a lazy object,
        // we need to call the `initializeLazyObject` method on the reflector of
        // the object to return the initialized object. If the object is not lazy,
        // this is a no-op.
        $this->proxy_factory = static fn(object $object): object => new \ReflectionClass($object)->initializeLazyObject(
            $container->get($object::class),
        );
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

    /**
     * @param class-string<MiddlewareInterface> $middleware_class
     */
    protected function pushMiddlewareClass(
        MutableMiddlewareRequestHandler $handler,
        string $middleware_class,
    ): MutableMiddlewareRequestHandler {
        $reflection = new \ReflectionClass($middleware_class);
        return $handler->push(match ($reflection->isInstantiable()) {
            true => $reflection->newLazyProxy($this->proxy_factory),
            false => LazyMiddleware::make($this->container, $middleware_class),
        });
    }
}
