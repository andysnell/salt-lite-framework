<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Types;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\DomesticPhoneNumber;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\E164;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\PhoneNumber;

class PhoneNumberType extends Type
{
    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return Types::PHONE_NUMBER;
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string|null
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof PhoneNumber) {
            return (string)$value->toE164();
        }

        throw InvalidType::new(
            $value,
            $this->getName(),
            [PhoneNumber::class],
        );
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): PhoneNumber
    {
        return DomesticPhoneNumber::tryFrom($value) ?? E164::make($value);
    }
}
