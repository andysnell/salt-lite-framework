<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface TerminableMiddleware extends MiddlewareInterface
{
    public function setFallbackRequestHandler(RequestHandlerInterface $handler): void;
}
