<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Handler;

use PhoneBurner\SaltLite\Framework\Http\Session\Handler\SessionHandlerDecorator;
use PhoneBurner\SaltLite\Http\Session\Exception\HttpSessionException;
use PhoneBurner\SaltLite\Http\Session\Exception\SessionWriteFailure;
use PhoneBurner\SaltLite\Http\Session\SessionHandler;
use PhoneBurner\SaltLite\Http\Session\SessionId;

class CompressingSessionHandlerDecorator extends SessionHandlerDecorator
{
    public function __construct(protected SessionHandler $handler)
    {
    }

    /**
     * Decompress the session data returned from the previous before returning it
     * Note that the data might not be valid compressed data, but we will just let
     * the empty string be returned in that case.
     */
    #[\Override]
    public function read(string|SessionId $id): string
    {
        $data = $this->handler->read($id);
        return $data === '' ? '' : (string)@\gzinflate($data);
    }

    /**
     * Compress the session data before writing it
     */
    #[\Override]
    public function write(string|SessionId $id, string $data): bool
    {
        try {
            return $this->handler->write($id, @\gzdeflate($data, 1) ?: throw new SessionWriteFailure(
                'Failed to compress session data with gzdeflate()',
            ));
        } catch (\Throwable $e) {
            throw $e instanceof HttpSessionException ? $e : new SessionWriteFailure(
                message: 'Failed to encode session data',
                previous: $e,
            );
        }
    }
}
