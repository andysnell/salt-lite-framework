<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Handler;

use PhoneBurner\SaltLite\Cryptography\ConstantTime;
use PhoneBurner\SaltLite\Http\Session\Exception\HttpSessionException;
use PhoneBurner\SaltLite\Http\Session\Exception\SessionWriteFailure;
use PhoneBurner\SaltLite\Http\Session\SessionHandler;
use PhoneBurner\SaltLite\Http\Session\SessionId;
use PhoneBurner\SaltLite\String\Encoding\Encoding;

final class EncodingSessionHandlerDecorator extends SessionHandlerDecorator
{
    public function __construct(
        protected SessionHandler $handler,
        private readonly Encoding $encoding,
    ) {
    }

    /**
     * Decrypt the session data returned from the previous before returning it
     */
    #[\Override]
    public function read(string|SessionId $id): string
    {
        $data = $this->handler->read($id);
        return $data === '' ? '' : ConstantTime::decode($this->encoding, $data);
    }

    /**
     * Encrypt the session data before writing it
     */
    #[\Override]
    public function write(string|SessionId $id, string $data): bool
    {
        try {
            return $this->handler->write($id, ConstantTime::encode($this->encoding, $data));
        } catch (\Throwable $e) {
            throw $e instanceof HttpSessionException ? $e : new SessionWriteFailure(
                message: 'Failed to encode session data',
                previous: $e,
            );
        }
    }
}
