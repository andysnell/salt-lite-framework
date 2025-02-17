<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Event;

use PhoneBurner\SaltLite\Framework\Logging\LogEntry;
use PhoneBurner\SaltLite\Framework\Logging\Loggable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class FallbackHandlerHandlingComplete implements Loggable
{
    public function __construct(
        public RequestHandlerInterface $request_handler,
        public ServerRequestInterface $request,
        public ResponseInterface $response,
    ) {
    }

    public function getLogEntry(): LogEntry
    {
        return new LogEntry(
            message: 'Handled Request with Fallback Handler: {fallback_handler}',
            context: [
                'fallback_handler' => $this->request_handler::class,
            ],
        );
    }
}
