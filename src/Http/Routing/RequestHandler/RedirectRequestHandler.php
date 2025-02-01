<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\RedirectResponse;
use PhoneBurner\SaltLite\Framework\Http\Routing\Match\RouteMatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RedirectRequestHandler implements RequestHandlerInterface
{
    public const string URI = 'redirect_with_uri';

    public const string STATUS_CODE = 'redirect_with_status_code';

    public const array ALLOWED_STATUS_CODES = [
        HttpStatus::MOVED_PERMANENTLY,
        HttpStatus::FOUND,
        HttpStatus::SEE_OTHER,
        HttpStatus::TEMPORARY_REDIRECT,
        HttpStatus::PERMANENT_REDIRECT,
    ];

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route_match = $request->getAttribute(RouteMatch::class);
        if (! $route_match instanceof RouteMatch) {
            throw new \LogicException('Request is Missing Required RouteMatch Attribute');
        }

        $uri = (string)($route_match->getAttributes()[self::URI] ?? null);
        if ($uri === '') {
            throw new \LogicException('Request has Invalid Redirect URI');
        }

        $status_code = $route_match->getAttributes()[self::STATUS_CODE] ?? null;
        if (! \in_array($status_code, self::ALLOWED_STATUS_CODES, true)) {
            throw new \LogicException('Request has Invalid Redirect Status Code');
        }

        return new RedirectResponse($uri, $status_code);
    }
}
