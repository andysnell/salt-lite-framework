<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Handler;

use PhoneBurner\SaltLite\Framework\Http\Session\SessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionId;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;

#[Internal]
final class NullSessionHandler extends SessionHandler
{
    public function read(string|SessionId $id): string
    {
        throw new \LogicException();
    }

    public function write(string|SessionId $id, string $data): bool
    {
        throw new \LogicException();
    }

    public function destroy(string|SessionId $id): bool
    {
        throw new \LogicException();
    }
}
