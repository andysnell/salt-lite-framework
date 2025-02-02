<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Cookie\CookieManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DecryptCookiesFromRequest implements MiddlewareInterface
{
    public function __construct(
        private readonly CookieManager $cookie_jar,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle(
            $this->cookie_jar->mutateRequest($request),
        );
    }
}
