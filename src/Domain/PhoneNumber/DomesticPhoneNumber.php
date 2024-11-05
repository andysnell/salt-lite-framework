<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeAware;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\Exception\InvalidPhoneNumber;
use TypeError;

/**
 * Value object representing a *valid* 10-digit NANP phone number.
 */
final readonly class DomesticPhoneNumber implements
    PhoneNumber,
    AreaCodeAware,
    \Stringable,
    \JsonSerializable
{
    public const string NANP_E164_REGEX = '/^\+1[2-9]\d{2}[2-9]\d{2}\d{4}$/';

    private E164 $phone_number;

    public AreaCode $area_code;

    private function __construct(E164|string $phone_number)
    {
        try {
            $this->phone_number = E164::make($phone_number);
        } catch (InvalidPhoneNumber $e) {
            throw new InvalidPhoneNumber('Not a Valid Domestic Number: ' . $phone_number, 0, $e);
        }

        if (! \preg_match(E164::NANP_REGEX, (string)$this->phone_number)) {
            throw new InvalidPhoneNumber('Not a Valid Domestic Number: ' . $phone_number);
        }

        $this->area_code = AreaCode::make($this->npa());
    }

    /**
     * @param PhoneNumber|\Stringable|string|int $phone_number
     */
    public static function make($phone_number): self
    {
        if ($phone_number instanceof self) {
            return $phone_number;
        }

        if ($phone_number instanceof PhoneNumber) {
            return new self($phone_number->toE164());
        }

        if (\is_string($phone_number) || $phone_number instanceof \Stringable || \is_int($phone_number)) {
            return new self((string)$phone_number);
        }

        throw new TypeError('PhoneNumber|Stringable|string|int, got ' . \get_debug_type($phone_number));
    }

    public static function tryFrom(mixed $phone_number): self|null
    {
        try {
            return self::make($phone_number);
        } catch (\Throwable) {
            return null;
        }
    }

    public function normalize(): string
    {
        return (string)$this->phone_number;
    }

    #[\Override]
    public function toE164(): E164
    {
        return $this->phone_number;
    }

    public function format(PhoneNumberFormat|null $format = null): string
    {
        return match ($format ?? PhoneNumberFormat::National) {
            PhoneNumberFormat::National => \sprintf("(%s) %s-%s", $this->npa(), $this->nxx(), \substr((string)$this->phone_number, 8)),
            PhoneNumberFormat::StripPrefix => \substr((string)$this->phone_number, 2),
            PhoneNumberFormat::E164 => (string)$this->phone_number,
            PhoneNumberFormat::International => \sprintf("+1 %s-%s-%s", $this->npa(), $this->nxx(), \substr((string)$this->phone_number, 8)),
            PhoneNumberFormat::Rfc3966 => \sprintf("tel:+1-%s-%s-%s", $this->npa(), $this->nxx(), \substr((string)$this->phone_number, 8)),
        };
    }

    #[\Override]
    public function getAreaCode(): AreaCode
    {
        return $this->area_code;
    }

    /**
     * @return int<200,999>
     */
    private function npa(): int
    {
        $npa = (int)\substr((string)$this->phone_number, 2, 3);
        \assert($npa >= 200 && $npa <= 999);
        return $npa;
    }

    /**
     * @return int<200,999>
     */
    public function nxx(): int
    {
        $nxx = (int)\substr((string)$this->phone_number, 5, 3);
        \assert($nxx >= 200 && $nxx <= 999);
        return $nxx;
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return (string)$this->phone_number;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->format(PhoneNumberFormat::StripPrefix);
    }

    public function __serialize(): array
    {
        return ['phone_number' => (string)$this->phone_number];
    }

    public function __unserialize(array $data): void
    {
        $phone_number = self::make($data['phone_number']);
        $this->phone_number = $phone_number->phone_number;
        $this->area_code = $phone_number->area_code;
    }
}
