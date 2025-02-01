<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\RequestHandler;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\GenericHttpExceptionResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\PageNotFoundResponse;
use PhoneBurner\SaltLite\Framework\Http\Routing\Match\RouteMatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorRequestHandler implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route_match = $request->getAttribute(RouteMatch::class);
        if (! $route_match instanceof RouteMatch) {
            return new PageNotFoundResponse();
        }

        $status_code = $this->getStatusCode($route_match);
        $reason_phrase = HttpReasonPhrase::lookup($status_code);

        return $reason_phrase === ''
            ? new GenericHttpExceptionResponse(HttpStatus::NOT_FOUND, HttpReasonPhrase::NOT_FOUND)
            : new GenericHttpExceptionResponse($status_code, $reason_phrase);
    }

    private function getStatusCode(RouteMatch $route_match): int
    {
        $parameter_status_code = (int)$route_match->getPathParameter('error');
        if ($parameter_status_code) {
            return $parameter_status_code;
        }

        return $route_match->getAttributes()[HttpStatus::class] ?? HttpStatus::NOT_FOUND;
    }
}
