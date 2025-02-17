<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\SerializationProhibited;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;

/**
 * @phpstan-require-implements BinaryString
 */
trait BinaryStringProhibitsSerialization
{
    final public function __toString(): string
    {
        throw new SerializationProhibited();
    }

    final public function __serialize(): array
    {
        throw new SerializationProhibited();
    }

    final public function __unserialize(array $data): never
    {
        throw new SerializationProhibited();
    }

    final public function jsonSerialize(): string
    {
        throw new SerializationProhibited();
    }
}
