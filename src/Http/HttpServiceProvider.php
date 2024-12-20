<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Http\Middleware\CatchExceptionalResponses;
use PhoneBurner\SaltLite\Framework\Http\Middleware\LazyMiddlewareRequestHandlerFactory;
use PhoneBurner\SaltLite\Framework\Http\Middleware\MiddlewareRequestHandlerFactory;
use PhoneBurner\SaltLite\Framework\Http\Middleware\TransformHttpExceptionResponses;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\CspViolationReportRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\ErrorRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\TransformerStrategies\TextResponseTransformerStrategy;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class HttpServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->set(
            HttpKernel::class,
            static function (ContainerInterface $container): HttpKernel {
                return new HttpKernel(
                    $container->get(RequestFactory::class),
                    $container->get(RequestHandlerInterface::class),
                    $container->get(EmitterInterface::class),
                    $container->get(LoggerInterface::class),
                    $container->get(BuildStage::class),
                );
            },
        );

        $container->set(
            RequestFactory::class,
            static function (ContainerInterface $container): RequestFactory {
                return new RequestFactory();
            },
        );

        $container->set(
            EmitterInterface::class,
            static function (ContainerInterface $container): EmitterInterface {
                return new SapiStreamEmitter();
            },
        );

        $container->set(
            MiddlewareRequestHandlerFactory::class,
            static function (ContainerInterface $container): MiddlewareRequestHandlerFactory {
                return new LazyMiddlewareRequestHandlerFactory($container);
            },
        );

        $container->set(
            RequestHandlerInterface::class,
            static function (ContainerInterface $container): RequestHandlerInterface {
                $config = $container->get(Configuration::class);
                return $container->get(MiddlewareRequestHandlerFactory::class)->queue(
                    $container->get($config->get('routing.fallback_request_handler') ?? NotFoundRequestHandler::class),
                    $config->get('middleware') ?? [],
                );
            },
        );

        $container->set(
            TransformHttpExceptionResponses::class,
            static function (ContainerInterface $container): TransformHttpExceptionResponses {
                $default = $container->get(Configuration::class)->get('app.exceptional_responses.default_transformer');
                return new TransformHttpExceptionResponses(
                    $container->get(LogTrace::class),
                    $default ?: TextResponseTransformerStrategy::class,
                );
            },
        );

        $container->set(
            CatchExceptionalResponses::class,
            static function (ContainerInterface $container): CatchExceptionalResponses {
                return new CatchExceptionalResponses(
                    $container->get(LoggerInterface::class),
                    $container->get(BuildStage::class),
                    $container->get(Context::class),
                );
            },
        );

        $container->set(
            NotFoundRequestHandler::class,
            static function (ContainerInterface $container): NotFoundRequestHandler {
                return new NotFoundRequestHandler($container->get(LoggerInterface::class));
            },
        );

        $container->set(
            CspViolationReportRequestHandler::class,
            static function (ContainerInterface $container): CspViolationReportRequestHandler {
                return new CspViolationReportRequestHandler($container->get(LoggerInterface::class));
            },
        );

        $container->set(
            ErrorRequestHandler::class,
            static function (ContainerInterface $container): ErrorRequestHandler {
                return new ErrorRequestHandler();
            },
        );
    }
}
