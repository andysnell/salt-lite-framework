<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeAware;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\Exception\InvalidPhoneNumber;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

/**
 * Value object representing a *valid* 10-digit NANP phone number.
 */
#[Contract]
final readonly class DomesticPhoneNumber implements
    PhoneNumber,
    AreaCodeAware,
    \Stringable,
    \JsonSerializable
{
    public AreaCode $area_code;

    private function __construct(public E164 $e164)
    {
        if (! \preg_match(E164::NANP_REGEX, (string)$this->e164)) {
            throw new InvalidPhoneNumber('Not a Valid Domestic Number: ' . $e164);
        }

        $this->area_code = AreaCode::make(\substr((string)$this->e164, 2, 3));
    }

    public static function make(NullablePhoneNumber|\Stringable|string|int $phone_number): self
    {
        return $phone_number instanceof self ? $phone_number : new self(E164::make($phone_number));
    }

    public static function tryFrom(mixed $phone_number): self|null
    {
        try {
            return $phone_number ? self::make($phone_number) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    #[\Override]
    public function toE164(): E164
    {
        return $this->e164;
    }

    public function format(PhoneNumberFormat|null $format = null): string
    {
        return match ($format ?? PhoneNumberFormat::National) {
            PhoneNumberFormat::National => \sprintf("(%s) %s-%s", $this->npa(), $this->nxx(), $this->line()),
            PhoneNumberFormat::StripPrefix => \substr((string)$this->e164, 2),
            PhoneNumberFormat::E164 => (string)$this->e164,
            PhoneNumberFormat::International => \sprintf("+1 %s-%s-%s", $this->npa(), $this->nxx(), $this->line()),
            PhoneNumberFormat::Rfc3966 => \sprintf("tel:+1-%s-%s-%s", $this->npa(), $this->nxx(), $this->line()),
        };
    }

    #[\Override]
    public function getAreaCode(): AreaCode
    {
        return $this->area_code;
    }

    /**
     * Returns the National Plan Area (Area Code) part of the NANP phone number.
     */
    public function npa(): string
    {
        return (string)$this->area_code->npa;
    }

    /**
     * Returns the Central Office (Exchange) Code part of the NANP phone number.
     */
    public function nxx(): string
    {
        return \substr((string)$this->e164, 5, 3);
    }

    /**
     * Returns the subscriber Line Code (last 4 digits) portion of the NANP phone number.
     */
    public function line(): string
    {
        return \substr((string)$this->e164, 8);
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->format(PhoneNumberFormat::E164);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->format(PhoneNumberFormat::E164);
    }

    public function __serialize(): array
    {
        return ['phone_number' => $this->format(PhoneNumberFormat::E164)];
    }

    public function __unserialize(array $data): void
    {
        $phone_number = self::make($data['phone_number']);
        $this->e164 = $phone_number->e164;
        $this->area_code = $phone_number->area_code;
    }
}
