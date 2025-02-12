<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\Clock\Clock;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Http\Cookie\CookieEncrypter;
use PhoneBurner\SaltLite\Framework\Http\Cookie\CookieJar;
use PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware\AddCookiesToResponse;
use PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware\DecryptCookiesFromRequest;
use PhoneBurner\SaltLite\Framework\Http\Middleware\CatchExceptionalResponses;
use PhoneBurner\SaltLite\Framework\Http\Middleware\LazyMiddlewareRequestHandlerFactory;
use PhoneBurner\SaltLite\Framework\Http\Middleware\MiddlewareRequestHandlerFactory;
use PhoneBurner\SaltLite\Framework\Http\Middleware\TransformHttpExceptionResponses;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\CspViolationReportRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\ErrorRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\TransformerStrategies\TextResponseTransformerStrategy;
use PhoneBurner\SaltLite\Framework\Http\Routing\Command\CacheRoutes;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\DefinitionList;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\LazyConfigDefinitionList;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteDispatcherFactory;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouter;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteResultFactory;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\AttachRouteToRequest;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteMiddleware;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\StaticFileRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\RouteProvider;
use PhoneBurner\SaltLite\Framework\Http\Routing\Router;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Crypto\AppKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Symmetric;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class HttpServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            Router::class,
            HttpKernel::class,
            RequestFactory::class,
            RequestHandlerFactory::class,
            EmitterInterface::class,
            MiddlewareRequestHandlerFactory::class,
            RequestHandlerInterface::class,
            TransformHttpExceptionResponses::class,
            CatchExceptionalResponses::class,
            NotFoundRequestHandler::class,
            CspViolationReportRequestHandler::class,
            ErrorRequestHandler::class,
            FastRouter::class,
            FastRouteDispatcherFactory::class,
            FastRouteResultFactory::class,
            DefinitionList::class,
            CacheRoutes::class,
            CookieEncrypter::class,
            CookieJar::class,
            DecryptCookiesFromRequest::class,
            AddCookiesToResponse::class,
            AttachRouteToRequest::class,
            DispatchRouteMiddleware::class,
            DispatchRouteRequestHandler::class,
            StaticFileRequestHandler::class,
        ];
    }

    public static function bind(): array
    {
        return [
            Router::class => FastRouter::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            HttpKernel::class,
            static fn(App $app): HttpKernel => new HttpKernel(
                $app->get(RequestFactory::class),
                $app->get(RequestHandlerInterface::class),
                $app->get(EmitterInterface::class),
                $app->get(LoggerInterface::class),
                $app->environment->stage,
            ),
        );

        $app->set(
            RequestFactory::class,
            static fn(App $app): RequestFactory => new RequestFactory(),
        );

        $app->set(
            RequestHandlerFactory::class,
            static fn(App $app): RequestHandlerFactory => new RequestHandlerFactory($app),
        );

        $app->set(
            EmitterInterface::class,
            static fn(App $app): EmitterInterface => new SapiStreamEmitter(),
        );

        $app->set(
            MiddlewareRequestHandlerFactory::class,
            static fn(App $app): MiddlewareRequestHandlerFactory => new LazyMiddlewareRequestHandlerFactory(
                $app->services,
            ),
        );

        $app->set(
            RequestHandlerInterface::class,
            static fn(App $app): RequestHandlerInterface => $app->get(MiddlewareRequestHandlerFactory::class)->queue(
                Type::of(RequestHandlerInterface::class, $app->get(
                    $app->config->get('http.routing.fallback_request_handler') ?? NotFoundRequestHandler::class,
                )),
                $app->config->get('http.middleware') ?? [],
            ),
        );

        $app->set(
            TransformHttpExceptionResponses::class,
            static fn(App $app): TransformHttpExceptionResponses => new TransformHttpExceptionResponses(
                $app->get(LogTrace::class),
                $app->config->get('http.exceptional_responses.default_transformer') ?: TextResponseTransformerStrategy::class,
            ),
        );

        $app->set(
            CatchExceptionalResponses::class,
            static fn(App $app): CatchExceptionalResponses => new CatchExceptionalResponses(
                $app->get(LoggerInterface::class),
                $app->environment->stage,
                $app->environment->context,
            ),
        );

        $app->set(
            NotFoundRequestHandler::class,
            static fn(App $app): NotFoundRequestHandler => new NotFoundRequestHandler(
                $app->get(LoggerInterface::class),
            ),
        );

        $app->set(
            CspViolationReportRequestHandler::class,
            static fn(App $app): CspViolationReportRequestHandler => new CspViolationReportRequestHandler(
                $app->get(LoggerInterface::class),
            ),
        );

        $app->set(
            ErrorRequestHandler::class,
            static fn(App $app): ErrorRequestHandler => new ErrorRequestHandler(),
        );

        $app->set(
            FastRouter::class,
            static fn(App $app): FastRouter => new FastRouter(
                $app->get(DefinitionList::class),
                $app->get(FastRouteDispatcherFactory::class),
                $app->get(FastRouteResultFactory::class),
            ),
        );

        $app->set(
            FastRouteDispatcherFactory::class,
            static fn(App $app): FastRouteDispatcherFactory => new FastRouteDispatcherFactory(
                $app->get(LoggerInterface::class),
                (bool)$app->config->get('http.routing.route_cache.enable'),
                (string)$app->config->get('http.routing.route_cache.filepath'),
            ),
        );

        $app->set(
            FastRouteResultFactory::class,
            static fn(App $app): FastRouteResultFactory => new FastRouteResultFactory(),
        );

        $app->set(
            DefinitionList::class,
            static fn(App $app): DefinitionList => LazyConfigDefinitionList::makeFromCallable(...\array_map(
                static fn(string $provider): RouteProvider => Type::of(RouteProvider::class, new $provider()),
                $app->config->get('http.routing.route_providers') ?? [],
            )),
        );

        $app->set(
            CacheRoutes::class,
            static fn(App $app): CacheRoutes => new CacheRoutes(
                $app->config,
                $app->get(FastRouter::class),
            ),
        );

        $app->set(
            CookieEncrypter::class,
            ghost(static fn(CookieEncrypter $ghost): null => $ghost->__construct(
                new Symmetric(),
                SharedKey::derive($app->get(AppKey::class), 'cookie'),
            )),
        );

        $app->set(
            CookieJar::class,
            ghost(static fn(CookieJar $ghost): null => $ghost->__construct(
                $app->get(CookieEncrypter::class),
                $app->get(Clock::class),
            )),
        );

        $app->set(
            DecryptCookiesFromRequest::class,
            static fn(App $app): DecryptCookiesFromRequest => new DecryptCookiesFromRequest(
                $app->get(CookieJar::class),
            ),
        );

        $app->set(
            AddCookiesToResponse::class,
            static fn(App $app): AddCookiesToResponse => new AddCookiesToResponse(
                $app->get(CookieJar::class),
            ),
        );

        $app->set(
            AttachRouteToRequest::class,
            static fn(App $app): AttachRouteToRequest => new AttachRouteToRequest(
                $app->get(Router::class),
            ),
        );

        $app->set(
            DispatchRouteMiddleware::class,
            static fn(App $app): DispatchRouteMiddleware => new DispatchRouteMiddleware(
                $app->get(MiddlewareRequestHandlerFactory::class),
            ),
        );

        $app->set(
            DispatchRouteRequestHandler::class,
            static fn(App $app): DispatchRouteRequestHandler => new DispatchRouteRequestHandler(
                $app->get(RequestHandlerFactory::class),
            ),
        );

        $app->set(
            StaticFileRequestHandler::class,
            static fn(App $app): StaticFileRequestHandler => new StaticFileRequestHandler(),
        );
    }
}
