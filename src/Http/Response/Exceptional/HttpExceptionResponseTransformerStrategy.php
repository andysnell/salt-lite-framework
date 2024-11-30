<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface HttpExceptionResponseTransformerStrategy
{
    public function transform(
        HttpExceptionResponse $exception,
        ServerRequestInterface $request,
        LogTrace $log_trace,
    ): ResponseInterface;
}
