<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Http\Psr7;
use PhoneBurner\SaltLite\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\SaltLite\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\HtmlResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\JsonResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\TextResponseTransformerStrategy;
use PhoneBurner\SaltLite\Logging\LogTrace;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TransformHttpExceptionResponses implements MiddlewareInterface
{
    /**
     * @param class-string<HttpExceptionResponseTransformerStrategy> $default_strategy
     */
    public function __construct(
        private readonly LogTrace $log_trace,
        private readonly string $default_strategy = TextResponseTransformerStrategy::class,
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof HttpExceptionResponse) {
            return $this->factory($request)->transform($response, $request, $this->log_trace);
        }

        return $response;
    }

    private function factory(ServerRequestInterface $request): HttpExceptionResponseTransformerStrategy
    {
        return match (true) {
            Psr7::expects($request, ContentType::JSON) => new JsonResponseTransformerStrategy(),
            Psr7::expects($request, ContentType::HTML) => new HtmlResponseTransformerStrategy(),
            Psr7::expects($request, ContentType::TEXT) => new TextResponseTransformerStrategy(),
            default => new $this->default_strategy(),
        };
    }
}
