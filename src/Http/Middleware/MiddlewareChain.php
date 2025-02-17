<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Event\FallbackHandlerHandlingComplete;
use PhoneBurner\SaltLite\Framework\Http\Event\FallbackHandlerHandlingStart;
use PhoneBurner\SaltLite\Framework\Http\Event\MiddlewareProcessingComplete;
use PhoneBurner\SaltLite\Framework\Http\Event\MiddlewareProcessingStart;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class MiddlewareChain implements MutableMiddlewareRequestHandler
{
    protected array $middleware_chain = [];

    abstract protected function next(): MiddlewareInterface|null;

    protected function __construct(
        protected RequestHandlerInterface $fallback_handler,
        protected EventDispatcherInterface|null $event_dispatcher = null,
    ) {
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

        if ($next_middleware instanceof TerminableMiddleware) {
            $next_middleware->setFallbackRequestHandler($this->fallback_handler);
        }

        if ($next_middleware instanceof MiddlewareInterface) {
            return $this->callNextMiddleware($next_middleware, $request);
        }

        return $this->callFallbackHandler($request);
    }

    private function callFallbackHandler(ServerRequestInterface $request): ResponseInterface
    {
        $this->event_dispatcher?->dispatch(new FallbackHandlerHandlingStart($this->fallback_handler, $request));

        try {
            $response = $this->fallback_handler->handle($request);
        } catch (\Throwable $e) {
            // If the exception is a response, then we should return it in
            // order to preserve the integrity of the middleware chain. Otherwise,
            // we could end up skipping important middleware like cookie and session
            // handling. Otherwise, we rethrow and expect the exception to be handled
            // by the CatchExceptionalResponses middleware.
            $response = $e instanceof ResponseInterface ? $e : throw $e;
        }

        $this->event_dispatcher?->dispatch(new FallbackHandlerHandlingComplete($this->fallback_handler, $request, $response));
        return $response;
    }

    private function callNextMiddleware(
        MiddlewareInterface $middleware,
        ServerRequestInterface $request,
    ): ResponseInterface {
        $this->event_dispatcher?->dispatch(new MiddlewareProcessingStart($middleware, $request));

        try {
            $response = $middleware->process($request, $this);
        } catch (\Throwable $e) {
            // If the exception is a response, then we should return it in
            // order to preserve the integrity of the middleware chain. Otherwise,
            // we could end up skipping important middleware like cookie and session
            // handling. Otherwise, we rethrow and expect the exception to be handled
            // by the CatchExceptionalResponses middleware.
            $response = $e instanceof ResponseInterface ? $e : throw $e;
        }

        $this->event_dispatcher?->dispatch(new MiddlewareProcessingComplete($middleware, $request, $response));
        return $response;
    }
}
