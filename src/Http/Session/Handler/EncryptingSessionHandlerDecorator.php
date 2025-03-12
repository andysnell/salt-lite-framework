<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Handler;

use PhoneBurner\SaltLite\Cryptography\Natrium;
use PhoneBurner\SaltLite\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Http\Session\Exception\HttpSessionException;
use PhoneBurner\SaltLite\Http\Session\Exception\SessionWriteFailure;
use PhoneBurner\SaltLite\Http\Session\SessionHandler;
use PhoneBurner\SaltLite\Http\Session\SessionId;

final class EncryptingSessionHandlerDecorator extends SessionHandlerDecorator
{
    public const string HKDF_CONTEXT = 'http-session-data';

    public function __construct(
        protected SessionHandler $handler,
        private readonly Natrium $natrium,
    ) {
    }

    /**
     * Decrypt the session data returned from the previous before returning it
     */
    #[\Override]
    public function read(string|SessionId $id): string
    {
        $data = $this->handler->read($id);
        return $data === '' ? '' : (string)$this->natrium->decrypt(new Ciphertext($data), self::HKDF_CONTEXT, $id);
    }

    /**
     * Encrypt the session data before writing it
     */
    #[\Override]
    public function write(string|SessionId $id, string $data): bool
    {
        try {
            return $this->handler->write($id, $this->natrium->encrypt($data, self::HKDF_CONTEXT, $id)->bytes());
        } catch (\Throwable $e) {
            throw $e instanceof HttpSessionException ? $e : new SessionWriteFailure(
                message: 'Failed to encrypt session data',
                previous: $e,
            );
        }
    }
}
