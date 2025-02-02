<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Middleware;

use PhoneBurner\SaltLite\Framework\Http\HttpServiceProvider;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ServerErrorResponse;
use PhoneBurner\SaltLite\Framework\Http\Session\Session;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionFactory;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

#[DefaultServiceProvider(HttpServiceProvider::class)]
class StartSession implements MiddlewareInterface
{
    public function __construct(
        private readonly SessionFactory $session_factory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $session = $this->session_factory->start();
        } catch (\RuntimeException $e) {
            $this->logger->error('failed to start session', ['exception' => $e]);
            return new ServerErrorResponse();
        }

        return $handler->handle(
            $request->withAttribute(Session::class, $session),
        );
    }
}
