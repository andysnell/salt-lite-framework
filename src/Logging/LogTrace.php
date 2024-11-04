<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly final class LogTrace implements \Stringable, \JsonSerializable
{
    private function __construct(public UuidInterface $uuid)
    {
    }

    /**
     * Create a log trace ID based on the "Timestamp First COMB" derivative of
     * the RFC 4122 Version 4 (Random) UUID. These UUIDs replace the first 48
     * bits with the microsecond timestamp, retaining 6 bits for the version/variant,
     * and 74 bits of randomness. This produces UUIDs that are monotonically
     * increasing and lexicographically sortable in both hex and byte formats.
     *
     * This allows us to be able to compare logged entries by when the request
     * started, and not necessarily when the log entry was made.
     */
    public static function make(): self
    {
        return new self(Uuid::ordered());
    }

    /**
     * Returns -1, 0, or 1 if less than, equal to, or greater than the other UUID
     */
    public function compare(self $other): int
    {
        return $this->uuid->compareTo($other->uuid);
    }

    /**
     * Returns the binary string representation of the UUID
     */
    public function getBytes(): string
    {
        return $this->uuid->getBytes();
    }

    /**
     * Returns the standard string representation of the UUID
     */
    public function toString(): string
    {
        return $this->uuid->toString();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Converts the instance to a string for PHP serialization, but as opposed
     * to how the `UUID` would normally serialize itself into a binary string,
     * we want to use the hex string version for maximum portability.
     *
     * @return array{uuid:string}
     */
    public function __serialize(): array
    {
        return ['uuid' => $this->toString()];
    }

    /**
     * @param array{uuid:string} $data
     */
    public function __unserialize(array $data): void
    {
        $this->uuid = Uuid::getOrderedFactory()->fromString($data['uuid']);
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
