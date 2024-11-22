<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing;

use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Routing\Definition\DefinitionList;
use PhoneBurner\SaltLite\Framework\Routing\Definition\LazyConfigDefinitionList;
use PhoneBurner\SaltLite\Framework\Routing\FastRoute\FastRouteDispatcherFactory;
use PhoneBurner\SaltLite\Framework\Routing\FastRoute\FastRouter;
use PhoneBurner\SaltLite\Framework\Routing\FastRoute\FastRouteResultFactory;
use PhoneBurner\SaltLite\Framework\Routing\RequestHandler\NullHandler;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class RoutingServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->bind(Router::class, FastRouter::class);
        $container->set(FastRouter::class, static function (ContainerInterface $container): FastRouter {
            return new FastRouter(
                $container->get(DefinitionList::class),
                $container->get(FastRouteDispatcherFactory::class),
                $container->get(FastRouteResultFactory::class),
            );
        });

        $container->set(
            FastRouteDispatcherFactory::class,
            static function (ContainerInterface $container): FastRouteDispatcherFactory {
                $cache_file = $container->get(Configuration::class)->get('routing.route_cache.filepath');
                $cache_enable = $container->get(BuildStage::class) !== BuildStage::Development
                    || $container->get(Configuration::class)->get('routing.route_cache.enable');

                return new FastRouteDispatcherFactory(
                    $container->get(LoggerInterface::class),
                    $cache_enable ? $cache_file : null,
                );
            },
        );

        $container->set(
            FastRouteResultFactory::class,
            static function (ContainerInterface $container): FastRouteResultFactory {
                return new FastRouteResultFactory();
            },
        );

        $container->set(
            DefinitionList::class,
            static function (ContainerInterface $container): DefinitionList {
                return LazyConfigDefinitionList::makeFromCallable(...\array_map(
                    static function (string $provider): RouteProvider {
                        $provider = new $provider();
                        \assert($provider instanceof RouteProvider);
                        return $provider;
                    },
                    $container->get(Configuration::class)->get('routing.route_providers') ?? [],
                ));
            },
        );

        $container->set(
            NullHandler::class,
            static function (ContainerInterface $container): NullHandler {
                return new NullHandler($container->get(LoggerInterface::class));
            },
        );
    }
}
