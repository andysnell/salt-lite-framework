<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\SaltLite\Http\Routing\RouteProvider;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class RoutingConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param class-string<RequestHandlerInterface> $fallback_handler
     * @param list<class-string<RouteProvider>> $route_providers
     */
    public function __construct(
        public bool $enable_cache = false,
        public string|null $cache_path = null,
        public array $route_providers = [],
        public string $fallback_handler = NotFoundRequestHandler::class,
    ) {
    }
}
