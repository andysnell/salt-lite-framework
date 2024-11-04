<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Definition;

use PhoneBurner\SaltLite\Framework\Domain\PhpSerializable;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @extends PhpSerializable<array{
 *     path: string,
 *     methods: array<HttpMethod::*>,
 *     attributes: array<string,string>,
 *     routes?: array<RouteDefinition>,
 *     groups?: array<RouteGroupDefinition>,
 *  }>
 */
interface Definition extends PhpSerializable
{
    public function with(callable ...$fns): self;

    public function withRoutePath(string $path): self;

    public function withMethod(HttpMethod ...$method): self;

    public function withAddedMethod(HttpMethod ...$method): self;

    public function withName(string $name): self;

    /**
     * @param class-string<RequestHandlerInterface> $handler_class
     */
    public function withHandler(string $handler_class): self;

    /**
     * @param class-string<MiddlewareInterface> ...$middleware
     */
    public function withMiddleware(string ...$middleware): self;

    public function withAttribute(string $name, mixed $value): self;

    public function withAttributes(array $attributes): self;

    public function withAddedAttributes(array $attributes): self;
}
