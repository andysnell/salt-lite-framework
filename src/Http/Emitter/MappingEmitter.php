<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Emitter;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use PhoneBurner\SaltLite\Http\Response\ServerSentEventsResponse;
use PhoneBurner\SaltLite\Http\Response\StreamResponse;
use Psr\Http\Message\ResponseInterface;

class MappingEmitter implements EmitterInterface
{
    public const int DEFAULT_BUFFER_SIZE = 8192;

    public function __construct(
        private readonly int $buffer_size = self::DEFAULT_BUFFER_SIZE,
    ) {
    }

    public function emit(ResponseInterface $response): bool
    {
        return (match (true) {
            $response instanceof ServerSentEventsResponse => new UnbufferedSapiStreamEmitter(),
            $response instanceof StreamResponse => new SapiStreamEmitter($this->buffer_size),
            default => new SapiEmitter(),
        })->emit($response);
    }
}
