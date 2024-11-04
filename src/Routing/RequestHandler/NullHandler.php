<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\RequestHandler;

use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\PageNotFoundResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Untested since we should never get here. Once we have an easy way to throw
 * common exceptions
 */
class NullHandler implements RequestHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->error('middleware tried to handle a request with ' . self::class, [
            'path' => (string)$request->getUri(),
            'stack' => \debug_backtrace(),
        ]);

        return new PageNotFoundResponse();
    }
}
