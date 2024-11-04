<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\FastRoute;

use FastRoute\Dispatcher;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Routing\Result\MethodNotAllowed;
use PhoneBurner\SaltLite\Framework\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Framework\Routing\Result\RouteNotFound;
use PhoneBurner\SaltLite\Framework\Routing\RouterResult;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;

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
