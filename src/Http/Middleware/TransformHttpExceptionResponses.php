<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponseTransformer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TransformHttpExceptionResponses implements MiddlewareInterface
{
    public function __construct(
        private readonly HttpExceptionResponseTransformer $response_transformer,
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response instanceof HttpExceptionResponse) {
            return $this->response_transformer->transform($response, $request);
        }

        return $response;
    }
}
