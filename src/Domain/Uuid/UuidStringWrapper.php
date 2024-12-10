<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Uuid;

use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @phpstan-require-implements UuidInterface
 */
trait UuidStringWrapper
{
    use UuidWrapper;

    /**
     * @var non-empty-string
     */
    private readonly string $uuid;

    public function __construct(\Stringable|string $uuid)
    {
        $this->uuid = Uuid::instance($uuid)->toString();
    }

    #[\Override]
    public function uuid(): UuidInterface
    {
        return Uuid::instance($this->uuid);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->uuid;
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->uuid;
    }

    #[\Override]
    public function __serialize(): array
    {
        return ['uuid' => $this->uuid];
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->uuid = $data['uuid'];
    }
}
