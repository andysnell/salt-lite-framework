<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute;

use FastRoute\Dispatcher;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteMatch;
use PhoneBurner\SaltLite\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Http\Routing\Result\MethodNotAllowed;
use PhoneBurner\SaltLite\Http\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Http\Routing\Result\RouteNotFound;
use PhoneBurner\SaltLite\Http\Routing\RouterResult;

#[Internal]
class FastRouteResultFactory
{
    public function make(FastRouteMatch $match): RouterResult
    {
        if ($match->getStatus() === Dispatcher::METHOD_NOT_ALLOWED) {
            return MethodNotAllowed::make(...\array_map(HttpMethod::instance(...), $match->getMethods()));
        }

        if ($match->getStatus() === Dispatcher::FOUND) {
            return RouteFound::make(
                \unserialize($match->getRouteData(), [
                    'allowed_classes' => true,
                ]),
                $match->getPathVars(),
            );
        }

        return RouteNotFound::make();
    }
}
