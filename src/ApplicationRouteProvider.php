<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework;

use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\CspViolationReportRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\ErrorRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\LogoutRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Http\Routing\Domain\StaticFile;
use PhoneBurner\SaltLite\Framework\Http\Routing\RouteProvider;
use PhoneBurner\SaltLite\Framework\Http\Session\Middleware\EnableHttpSession;

use function PhoneBurner\SaltLite\Framework\path;

/**
 * @codeCoverageIgnore
 */
class ApplicationRouteProvider implements RouteProvider
{
    #[\Override]
    public function __invoke(): array
    {
        return [
            RouteDefinition::file('/', new StaticFile(
                path('/resources/views/welcome.html'),
                ContentType::HTML,
            ))->withMiddleware(EnableHttpSession::class),

            RouteDefinition::all('/logout')
                ->withHandler(LogoutRequestHandler::class)
                ->withMiddleware(EnableHttpSession::class)
                ->withName('logout'),

            RouteDefinition::post('/csp')
                ->withHandler(CspViolationReportRequestHandler::class),

            RouteDefinition::get('/errors[/{error}]')
                ->withHandler(ErrorRequestHandler::class),
        ];
    }
}
