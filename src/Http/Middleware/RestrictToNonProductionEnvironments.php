<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\App\BuildStage;
use PhoneBurner\SaltLite\Http\Response\Exceptional\ServerErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RestrictToNonProductionEnvironments implements MiddlewareInterface
{
    public function __construct(private readonly BuildStage $stage)
    {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->stage === BuildStage::Production
            ? new ServerErrorResponse()
            : $handler->handle($request);
    }
}
