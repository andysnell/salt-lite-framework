<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

/**
 * Value object representing an empty phone number.
 */
final readonly class NullPhoneNumber implements NullablePhoneNumber, NullablePhoneNumberAware
{
    public static function make(): self
    {
        static $cache;
        return $cache ??= new self();
    }

    #[\Override]
    public function getPhoneNumber(): self
    {
        return $this;
    }

    #[\Override]
    public function toE164(): E164|null
    {
        return null;
    }
}
