<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Uuid;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidString implements UuidInterface
{
    use UuidWrapper;

    public function __construct(private readonly UuidInterface $uuid)
    {
    }

    #[\Override]
    protected function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    #[\Override]
    public function __serialize(): array
    {
        return ['uuid' => $this->uuid->toString()];
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->uuid = Uuid::fromString($data['uuid']);
    }
}
