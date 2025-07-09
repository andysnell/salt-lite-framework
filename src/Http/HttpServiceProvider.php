<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Container\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\SaltLite\Cryptography\Natrium;
use PhoneBurner\SaltLite\Framework\Http\Cookie\CookieEncrypter;
use PhoneBurner\SaltLite\Framework\Http\Cookie\Middleware\ManageCookies;
use PhoneBurner\SaltLite\Framework\Http\Emitter\MappingEmitter;
use PhoneBurner\SaltLite\Framework\Http\Middleware\CatchExceptionalResponses;
use PhoneBurner\SaltLite\Framework\Http\Middleware\TransformHttpExceptionResponses;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\CspViolationReportRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\ErrorRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\LogoutRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\Command\CacheRoutesCommand;
use PhoneBurner\SaltLite\Framework\Http\Routing\Command\ListRoutesCommand;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteDispatcherFactory;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteResultFactory;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRouter;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\AttachRouteToRequest;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteMiddleware;
use PhoneBurner\SaltLite\Framework\Http\Routing\Middleware\DispatchRouteRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionHandlerServiceFactory;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionManager;
use PhoneBurner\SaltLite\Http\Cookie\CookieJar;
use PhoneBurner\SaltLite\Http\Middleware\LazyMiddlewareRequestHandlerFactory;
use PhoneBurner\SaltLite\Http\Middleware\MiddlewareRequestHandlerFactory;
use PhoneBurner\SaltLite\Http\RequestFactory;
use PhoneBurner\SaltLite\Http\RequestHandlerFactory;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\HtmlResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\JsonResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\TextResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Routing\Definition\DefinitionList;
use PhoneBurner\SaltLite\Http\Routing\Definition\LazyConfigDefinitionList;
use PhoneBurner\SaltLite\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\SaltLite\Http\Routing\RequestHandler\StaticFileRequestHandler;
use PhoneBurner\SaltLite\Http\Routing\RouteProvider;
use PhoneBurner\SaltLite\Http\Routing\Router;
use PhoneBurner\SaltLite\Http\Session\SessionHandler;
use PhoneBurner\SaltLite\Http\Session\SessionManager as SessionManagerContract;
use PhoneBurner\SaltLite\Logging\LogTrace;
use PhoneBurner\SaltLite\Time\Clock\Clock;
use PhoneBurner\SaltLite\Type\Type;
use Psr\EventDispatcher\EventDispatcherInterface;
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
            ListRoutesCommand::class,
            CacheRoutesCommand::class,
            CookieEncrypter::class,
            CookieJar::class,
            ManageCookies::class,
            AttachRouteToRequest::class,
            DispatchRouteMiddleware::class,
            DispatchRouteRequestHandler::class,
            StaticFileRequestHandler::class,
            JsonResponseTransformerStrategy::class,
            HtmlResponseTransformerStrategy::class,
            TextResponseTransformerStrategy::class,
            SessionHandler::class,
            SessionManager::class,
            SessionManagerContract::class,
            LogoutRequestHandler::class,
        ];
    }

    public static function bind(): array
    {
        return [
            Router::class => FastRouter::class,
            SessionManagerContract::class => SessionManager::class,
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
                $app->environment->stage,
                $app->get(EventDispatcherInterface::class),
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
            static fn(App $app): EmitterInterface => new MappingEmitter(),
        );

        $app->set(
            MiddlewareRequestHandlerFactory::class,
            static fn(App $app): MiddlewareRequestHandlerFactory => new LazyMiddlewareRequestHandlerFactory(
                $app->services,
                $app->get(EventDispatcherInterface::class),
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

        $app->set(JsonResponseTransformerStrategy::class, NewInstanceServiceFactory::singleton());

        $app->set(HtmlResponseTransformerStrategy::class, NewInstanceServiceFactory::singleton());

        $app->set(TextResponseTransformerStrategy::class, NewInstanceServiceFactory::singleton());

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

        $app->set(ErrorRequestHandler::class, NewInstanceServiceFactory::singleton());

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
                $app->config->get('http.routing'),
            ),
        );

        $app->set(FastRouteResultFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(
            DefinitionList::class,
            static fn(App $app): DefinitionList => LazyConfigDefinitionList::makeFromCallable(...\array_map(
                static fn(string $provider): RouteProvider => Type::of(RouteProvider::class, new $provider()),
                $app->config->get('http.routing.route_providers') ?? [],
            )),
        );

        $app->set(
            ListRoutesCommand::class,
            static fn(App $app): ListRoutesCommand => new ListRoutesCommand($app->get(DefinitionList::class)),
        );

        $app->set(
            CacheRoutesCommand::class,
            static fn(App $app): CacheRoutesCommand => new CacheRoutesCommand(
                $app->config,
                $app->get(FastRouter::class),
            ),
        );

        $app->set(
            CookieEncrypter::class,
            ghost(static fn(CookieEncrypter $ghost): null => $ghost->__construct(
                $app->get(Natrium::class),
            )),
        );

        $app->set(CookieJar::class, NewInstanceServiceFactory::singleton());

        $app->set(
            ManageCookies::class,
            static fn(App $app): ManageCookies => new ManageCookies(
                $app->get(CookieJar::class),
                $app->get(CookieEncrypter::class),
                $app->get(Clock::class),
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
            LogoutRequestHandler::class,
            static fn(App $app): LogoutRequestHandler => new LogoutRequestHandler(
                $app->get(SessionManager::class),
                $app->get(EventDispatcherInterface::class),
                $app->config->get('http.logout_redirect_url') ?? LogoutRequestHandler::DEFAULT_REDIRECT,
            ),
        );

        $app->set(StaticFileRequestHandler::class, NewInstanceServiceFactory::singleton());

        $app->set(SessionHandler::class, new SessionHandlerServiceFactory());

        $app->set(
            SessionManager::class,
            static fn(App $app): SessionManager => new SessionManager(
                $app->get(SessionHandler::class),
                $app->config->get('http.session'),
                $app->get(Natrium::class),
                $app->get(LoggerInterface::class),
            ),
        );
    }
}
