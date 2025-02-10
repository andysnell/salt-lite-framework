<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AddCookiesToResponse implements MiddlewareInterface
{
    public function __construct(
        private readonly CookieJar $cookie_jar,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Fully resolve the response from the handler first, to get accurate count
        $response = $handler->handle($request);

        // Skip mutating the response if there are no cookies in the queue
        return \count($this->cookie_jar) === 0 ? $response : $this->cookie_jar->mutateResponse($response);
    }
}
