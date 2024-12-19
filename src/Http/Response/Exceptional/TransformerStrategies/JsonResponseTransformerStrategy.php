<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\TransformerStrategies;

use PhoneBurner\SaltLite\Framework\Http\Response\ApiProblemResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use Psr\Http\Message\ServerRequestInterface;

class JsonResponseTransformerStrategy implements HttpExceptionResponseTransformerStrategy
{
    public function transform(
        HttpExceptionResponse $exception,
        ServerRequestInterface $request,
        LogTrace $log_trace,
    ): ApiProblemResponse {
        return new ApiProblemResponse($exception->getStatusCode(), $exception->getStatusTitle(), [
            'log_trace' => $log_trace->toString(),
            'detail' => $exception->getStatusDetail() ?: null,
            ...$exception->getAdditional(),
        ], $exception->getHeaders());
    }
}
