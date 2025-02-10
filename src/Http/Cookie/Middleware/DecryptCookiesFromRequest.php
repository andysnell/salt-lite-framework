<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DecryptCookiesFromRequest implements MiddlewareInterface
{
    public function __construct(
        private readonly CookieJar $cookie_jar,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle(match ($request->getCookieParams()) {
            [] => $request,
            default => $this->cookie_jar->mutateRequest($request),
        });
    }
}
