<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\ApplicationRouteProvider;
use PhoneBurner\SaltLite\Framework\Http\Config\HttpConfigStruct;
use PhoneBurner\SaltLite\Framework\Http\Config\RoutingConfigStruct;
use PhoneBurner\SaltLite\Framework\Http\Config\SessionConfigStruct;
use PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware\ManageCookies;
use PhoneBurner\SaltLite\Framework\Http\Middleware\CatchExceptionalResponses;
use PhoneBurner\SaltLite\Framework\Http\Middleware\EvaluateWrappedResponseFactories;
use PhoneBurner\SaltLite\Framework\Http\Middleware\TransformHttpExceptionResponses;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\AttachRouteToRequest;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteMiddleware;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionHandlerType;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\JsonResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\SaltLite\Time\Ttl;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;

return [
    'http' => new HttpConfigStruct(
        exceptional_response_default_transformer: JsonResponseTransformerStrategy::class,
        logout_redirect_url: '/',
        routing: new RoutingConfigStruct(
            enable_cache: (bool)env('SALT_ENABLE_ROUTE_CACHE', true, false),
            cache_path: path('/storage/bootstrap/routes.cache.php'),
            route_providers: [
                // Application Route Providers
                ApplicationRouteProvider::class, // IMPORTANT: replace this default with the application version
            ],
            fallback_handler: NotFoundRequestHandler::class,
        ),
        session: new SessionConfigStruct(
            SessionHandlerType::Redis,
            Ttl::hours(1),
            lock_sessions: true,
            encrypt: false,
            compress: false,
            encoding: null,
            add_xsrf_token_cookie: true,
        ),
        middleware: [
            TransformHttpExceptionResponses::class,
            CatchExceptionalResponses::class,
            ManageCookies::class,
            EvaluateWrappedResponseFactories::class,
            AttachRouteToRequest::class,
            DispatchRouteMiddleware::class,
            DispatchRouteRequestHandler::class,
        ],
    ),
];
