<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use PhoneBurner\SaltLite\Framework\Domain\Uuid\UuidWrapper;
use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly final class LogTrace implements UuidInterface
{
    use UuidWrapper;

    private function __construct(public UuidInterface $uuid)
    {
    }

    #[\Override]
    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * Create a log trace ID based on an RFC 4122 v7 UUID. This produces UUIDs
     * that are monotonically increasing and lexicographically sortable in both
     * hex and byte formats. This allows us to be able to compare logged entries
     * by when the request started, and not necessarily when the log entry was made.
     */
    public static function make(): self
    {
        return new self(Uuid::ordered());
    }

    /**
     * Converts the instance to a string for PHP serialization, but as opposed
     * to how the `UUID` would normally serialize itself into a binary string,
     * we want to use the hex string version for maximum portability.
     *
     * @return array{uuid:string}
     */
    #[\Override]
    public function __serialize(): array
    {
        return ['uuid' => $this->toString()];
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->uuid = Uuid::instance($data['uuid']);
    }
}
