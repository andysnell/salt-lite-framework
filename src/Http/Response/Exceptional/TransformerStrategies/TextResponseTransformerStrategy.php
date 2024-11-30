<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\TransformerStrategies;

use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\GenericHttpExceptionResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\SaltLite\Framework\Http\Response\TextResponse;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TextResponseTransformerStrategy implements HttpExceptionResponseTransformerStrategy
{
    public function transform(
        ResponseInterface $exception,
        ServerRequestInterface $request,
        LogTrace $log_trace,
    ): TextResponse {
        if ($exception instanceof GenericHttpExceptionResponse) {
            $exception = $exception->getWrapped();
        }

        return $exception instanceof TextResponse ? $exception : new TextResponse(
            \sprintf('HTTP %s: %s', $exception->getStatusCode(), $exception->getReasonPhrase()),
            $exception->getStatusCode(),
            $exception->getHeaders(),
        );
    }
}
