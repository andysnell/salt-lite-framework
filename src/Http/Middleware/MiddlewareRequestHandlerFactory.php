<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use Psr\Http\Server\RequestHandlerInterface;

/**
 * Given an array of MiddlewareInterface instances, MiddlewareInterface class
 * name strings, or strings matching the values of the `MiddlewareGroup` class
 * constants, implementations of this interface should produce RequestHandler
 * structures composed of the passed instances, instances of the class name, or
 * MiddlewareInterface instances that will resolve to instances of the class
 * name when process is called.
 */
interface MiddlewareRequestHandlerFactory
{
    public function queue(RequestHandlerInterface $fallback_handler, iterable $middleware_chain = []): MiddlewareQueue;

    public function stack(RequestHandlerInterface $fallback_handler, iterable $middleware_chain = []): MiddlewareStack;
}
