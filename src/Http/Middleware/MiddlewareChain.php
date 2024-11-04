<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class MiddlewareChain implements MutableMiddlewareRequestHandler
{
    protected array $middleware_chain = [];

    abstract protected function next(): MiddlewareInterface|null;

    protected function __construct(protected RequestHandlerInterface $fallback_handler)
    {
    }

    #[\Override]
    public function push(MiddlewareInterface $middleware): static
    {
        $this->middleware_chain[] = $middleware;
        return $this;
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $next_middleware = $this->next();
        if (! $next_middleware) {
            return $this->fallback_handler->handle($request);
        }

        if ($next_middleware instanceof TerminableMiddleware) {
            $next_middleware->setFallbackRequestHandler($this->fallback_handler);
        }

        return $next_middleware->process($request, $this);
    }
}
