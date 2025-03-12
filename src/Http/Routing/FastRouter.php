<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use LogicException;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteDispatcherFactory;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteMatch;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteResultFactory;
use PhoneBurner\SaltLite\Http\Routing\Definition\DefinitionList;
use PhoneBurner\SaltLite\Http\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Http\Routing\Result\RouteNotFound;
use PhoneBurner\SaltLite\Http\Routing\Router;
use PhoneBurner\SaltLite\Http\Routing\RouterResult;
use Psr\Http\Message\ServerRequestInterface;

#[Internal]
class FastRouter implements Router
{
    private Dispatcher|null $dispatcher = null;

    public function __construct(
        private readonly DefinitionList $definition_list,
        private readonly FastRouteDispatcherFactory $dispatcher_factory,
        private readonly FastRouteResultFactory $found_route_factory,
    ) {
    }

    #[\Override]
    public function resolveByName(string $name): RouterResult
    {
        try {
            $definition = $this->definition_list->getNamedRoute($name);
            return RouteFound::make($definition);
        } catch (LogicException) {
            return RouteNotFound::make();
        }
    }

    #[\Override]
    public function resolveForRequest(ServerRequestInterface $request): RouterResult
    {
        return $this->found_route_factory->make(FastRouteMatch::make(
            $this->dispatcher()->dispatch($request->getMethod(), $request->getUri()->getPath()),
        ));
    }

    public function dispatcher(): Dispatcher
    {
        return $this->dispatcher ??= $this->dispatcher_factory->make(function (RouteCollector $collector): void {
            foreach ($this->definition_list as $definition) {
                $collector->addRoute(
                    $definition->getMethods(),
                    $definition->getRoutePath(),
                    \serialize($definition),
                );
            }
        });
    }
}
