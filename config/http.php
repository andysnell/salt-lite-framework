<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware\AddCookiesToResponse;
use PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware\DecryptCookiesFromRequest;
use PhoneBurner\SaltLite\Framework\Http\Middleware\CatchExceptionalResponses;
use PhoneBurner\SaltLite\Framework\Http\Middleware\EvaluateWrappedResponseFactories;
use PhoneBurner\SaltLite\Framework\Http\Middleware\TransformHttpExceptionResponses;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\TransformerStrategies\JsonResponseTransformerStrategy;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\AttachRouteToRequest;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteMiddleware;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\NotFoundRequestHandler;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;

return [
    'http' => [
        'exceptional_responses' => [
            'default_transformer' => JsonResponseTransformerStrategy::class,
        ],
        'routing' => [
            'route_cache' => [
                'enable' => (bool)env('SALT_ENABLE_ROUTE_CACHE', true, false),
                'filepath' => path('/storage/bootstrap/routes.cache.php'),
            ],
            'route_providers' => [
                // Application Route Providers,
            ],
            'fallback_handler' => NotFoundRequestHandler::class,
        ],
        'middleware' => [
            TransformHttpExceptionResponses::class,
            CatchExceptionalResponses::class,
            DecryptCookiesFromRequest::class,
            AddCookiesToResponse::class,
            EvaluateWrappedResponseFactories::class,
            AttachRouteToRequest::class,
            DispatchRouteMiddleware::class,
            DispatchRouteRequestHandler::class,
        ],
    ],
];
