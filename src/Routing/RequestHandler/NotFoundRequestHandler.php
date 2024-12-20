<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\RequestHandler;

use PhoneBurner\SaltLite\Framework\Domain\Ip\IpAddress;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\NotFoundResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * This should be the default fallback handler for all requests that are not
 * otherwise routed or handled by the application in some way.
 */
class NotFoundRequestHandler implements RequestHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->notice('Not Found: {path}', [
            'path' => (string)$request->getUri(),
            'ip_address' => $request->getAttribute(IpAddress::class),
        ]);

        return new NotFoundResponse();
    }
}
