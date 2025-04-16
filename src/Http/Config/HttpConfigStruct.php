<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Http\Response\Exceptional\HttpExceptionResponseTransformerStrategy;
use PhoneBurner\SaltLite\Http\Response\Exceptional\TransformerStrategies\JsonResponseTransformerStrategy;
use Psr\Http\Server\MiddlewareInterface;

final readonly class HttpConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param class-string<HttpExceptionResponseTransformerStrategy> $exceptional_response_default_transformer
     * @param list<class-string<MiddlewareInterface>> $middleware
     */
    public function __construct(
        public string $exceptional_response_default_transformer = JsonResponseTransformerStrategy::class,
        public string $logout_redirect_url = '/',
        public RoutingConfigStruct $routing = new RoutingConfigStruct(),
        public SessionConfigStruct $session = new SessionConfigStruct(),
        public array $middleware = [],
    ) {
    }
}
