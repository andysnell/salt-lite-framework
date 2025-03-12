<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\Middleware;

use PhoneBurner\SaltLite\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Http\Response\EmptyResponse;
use PhoneBurner\SaltLite\Http\Response\Exceptional\MethodNotAllowedResponse;
use PhoneBurner\SaltLite\Http\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Http\Routing\Result\MethodNotAllowed;
use PhoneBurner\SaltLite\Http\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Http\Routing\Router;
use PhoneBurner\SaltLite\Time\TimeConstant;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AttachRouteToRequest implements MiddlewareInterface
{
    public function __construct(private readonly Router $finder)
    {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->finder->resolveForRequest($request);

        if ($result instanceof MethodNotAllowed) {
            if ($request->getMethod() === HttpMethod::Options->value) {
                return $this->handleOptionsRequest($request, $result);
            }

            return new MethodNotAllowedResponse(...$result->getAllowedMethods());
        }

        if ($result instanceof RouteFound) {
            $request = $request->withAttribute(RouteMatch::class, $result->getRouteMatch());
        }

        return $handler->handle($request);
    }

    private function handleOptionsRequest(ServerRequestInterface $request, MethodNotAllowed $result): ResponseInterface
    {
        $allowed_methods = \array_column([HttpMethod::Options, ...$result->getAllowedMethods()], 'value');
        $allowed_methods = \implode(', ', \array_unique($allowed_methods));

        $allowed_headers = \explode(',', $request->getHeaderLine(HttpHeader::ACCESS_CONTROL_REQUEST_HEADERS));
        $allowed_headers = [HttpHeader::AUTHORIZATION, HttpHeader::COOKIE, ...$allowed_headers];
        $allowed_headers = \array_map(\trim(...), $allowed_headers);
        $allowed_headers = \array_map(\strtolower(...), $allowed_headers);
        $allowed_headers = \implode(',', \array_unique($allowed_headers));

        $headers = [
            HttpHeader::ALLOW => $allowed_methods,
            HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS => $allowed_headers,
            HttpHeader::ACCESS_CONTROL_MAX_AGE => TimeConstant::SECONDS_IN_DAY,
            HttpHeader::ACCESS_CONTROL_ALLOW_METHODS => $allowed_methods,
        ];

        if ($request->hasHeader(HttpHeader::ORIGIN)) {
            $headers[HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN] = $request->getHeaderLine(HttpHeader::ORIGIN);
            $headers[HttpHeader::VARY] = $request->getHeaderLine(HttpHeader::ORIGIN);
        }

        return new EmptyResponse(headers: $headers);
    }
}
