<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Emitter;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitterTrait;
use PhoneBurner\SaltLite\Http\Response\ServerSentEventsResponse;
use PhoneBurner\SaltLite\Time\Ttl;
use Psr\Http\Message\ResponseInterface;

class UnbufferedSapiStreamEmitter implements EmitterInterface
{
    use SapiEmitterTrait;

    public const int DEFAULT_BUFFER_SIZE = 8192;

    public function emit(ResponseInterface $response): bool
    {
        // Reset the time limit so the runtime does not terminate early
        if ($response instanceof ServerSentEventsResponse) {
            \set_time_limit($response->ttl == Ttl::max() ? 0 : $response->ttl->seconds);
        }

        $this->assertNoPreviousOutput();
        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        \flush();

        $body = $response->getBody();
        while (! $body->eof()) {
            echo $body->read(self::DEFAULT_BUFFER_SIZE);
            \ob_flush();
            \flush();
        }

        return true;
    }
}
