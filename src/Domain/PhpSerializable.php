<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain;

/**
 * This interface should be implemented by objects intended to be serialized
 * as PHP objects, especially if the data will be persisted for any length of
 * time.
 *
 * @template T of array
 */
interface PhpSerializable
{
    /**
     * @return T
     */
    public function __serialize(): array;

    /**
     * @param T&array $data
     */
    public function __unserialize(array $data): void;
}
