<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\Exception\InvalidPhoneNumber;

/**
 * This is the lowest level phone number object we should be working with. It
 * represents a *possible* number in e.164 format. Other services classes or
 * wrapping value objects should deal with the validity of the phone number.
 */
final readonly class E164 implements
    \Stringable,
    \JsonSerializable,
    PhoneNumber,
    PhoneNumberAware
{
    public const string INTL_REGEX = '/^\+[2-9]\d{6,14}$/';

    public const string NANP_REGEX = '/^\+1[2-9]\d{2}[2-9]\d{2}\d{4}$/';

    private string $phone_number;

    private function __construct(string $phone_number)
    {
        $this->phone_number = self::filter($phone_number)
            ?? throw new InvalidPhoneNumber('Invalid E164 Phone Number');
    }

    private static function filter(string $phone_number): string|null
    {
        // Shortcut if we have a NANP number already in E164 format
        if (\strlen($phone_number) === 12 && \str_starts_with($phone_number, '+1') && \preg_match(self::NANP_REGEX, $phone_number)) {
            return $phone_number;
        }

        $phone_number = (string)\preg_replace('/\D/', '', $phone_number);

        // Assume 10-digit numbers that match the NANP pattern are US numbers
        if (\strlen($phone_number) === 10 && \preg_match(self::NANP_REGEX, '+1' . $phone_number)) {
            return '+1' . $phone_number;
        }

        $phone_number = '+' . $phone_number;

        // If the first digit is a "1", it has to be a NANP Number
        if (\str_starts_with($phone_number, '+1')) {
            return \preg_match(self::NANP_REGEX, $phone_number) ? $phone_number : null;
        }

        return \preg_match(self::INTL_REGEX, $phone_number) ? $phone_number : null;
    }

    public static function make(NullablePhoneNumber|\Stringable|string|int $phone_number): self
    {
        return match (true) {
            $phone_number instanceof self => $phone_number,
            $phone_number instanceof NullablePhoneNumber => $phone_number->toE164() ?? throw new InvalidPhoneNumber('Invalid E164 Phone Number'),
            default => new self((string)$phone_number),
        };
    }

    public static function tryFrom(mixed $phone_number): self|null
    {
        try {
            return $phone_number !== null ? self::make($phone_number) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    #[\Override]
    public function getPhoneNumber(): self
    {
        return $this;
    }

    #[\Override]
    public function toE164(): self
    {
        return $this;
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->phone_number;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->phone_number;
    }
}
