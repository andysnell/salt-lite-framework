<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Handler;

use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionHandler;
use PhoneBurner\SaltLite\Http\Session\SessionId;

#[Internal]
class InMemorySessionHandler extends SessionHandler
{
    private array $sessions = [];

    public function read(string|SessionId $id): string
    {
        return $this->sessions[(string)$id] ?? '';
    }

    public function write(string|SessionId $id, string $data): bool
    {
        $this->sessions[(string)$id] = $data;
        return true;
    }

    public function destroy(string|SessionId $id): bool
    {
        unset($this->sessions[(string)$id]);
        return true;
    }
}
