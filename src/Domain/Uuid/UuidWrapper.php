<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Uuid;

use DateTimeInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Fields\FieldsInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\UuidInterface;

/**
 * @phpstan-require-implements UuidInterface
 */
trait UuidWrapper
{
    abstract protected function uuid(): UuidInterface;

    abstract public function __serialize(): array;

    abstract public function __unserialize(array $data): void;

    public function compareTo(UuidInterface $other): int
    {
        return $this->uuid()->compareTo($other);
    }

    public function equals(object|null $other): bool
    {
        return $this->uuid()->equals($other);
    }

    public function getBytes(): string
    {
        return $this->uuid()->getBytes();
    }

    public function getFields(): FieldsInterface
    {
        return $this->uuid()->getFields();
    }

    public function getHex(): Hexadecimal
    {
        return $this->uuid()->getHex();
    }

    public function getInteger(): IntegerObject
    {
        return $this->uuid()->getInteger();
    }

    public function getUrn(): string
    {
        return $this->uuid()->getUrn();
    }

    public function toString(): string
    {
        return $this->uuid()->toString();
    }

    public function __toString(): string
    {
        return (string)$this->uuid();
    }

    public function jsonSerialize(): string
    {
        return $this->uuid()->jsonSerialize();
    }

    /**
     * @deprecated
     */
    public function getNumberConverter(): NumberConverterInterface
    {
        return $this->uuid()->getNumberConverter();
    }

    /**
     * @deprecated
     */
    public function getFieldsHex(): array
    {
        return $this->uuid()->getFieldsHex();
    }

    /**
     * @deprecated
     */
    public function getClockSeqHiAndReservedHex(): string
    {
        return $this->uuid()->getClockSeqHiAndReservedHex();
    }

    /**
     * @deprecated
     */
    public function getClockSeqLowHex(): string
    {
        return $this->uuid()->getClockSeqLowHex();
    }

    /**
     * @deprecated
     */
    public function getClockSequenceHex(): string
    {
        return $this->uuid()->getClockSequenceHex();
    }

    /**
     * @deprecated
     */
    public function getDateTime(): DateTimeInterface
    {
        return $this->uuid()->getDateTime();
    }

    /**
     * @deprecated
     */
    public function getLeastSignificantBitsHex(): string
    {
        return $this->uuid()->getLeastSignificantBitsHex();
    }

    /**
     * @deprecated
     */
    public function getMostSignificantBitsHex(): string
    {
        return $this->uuid()->getMostSignificantBitsHex();
    }

    /**
     * @deprecated
     */
    public function getNodeHex(): string
    {
        return $this->uuid()->getNodeHex();
    }

    /**
     * @deprecated
     */
    public function getTimeHiAndVersionHex(): string
    {
        return $this->uuid()->getTimeHiAndVersionHex();
    }

    /**
     * @deprecated
     */
    public function getTimeLowHex(): string
    {
        return $this->uuid()->getTimeLowHex();
    }

    /**
     * @deprecated
     */
    public function getTimeMidHex(): string
    {
        return $this->uuid()->getTimeMidHex();
    }

    /**
     * @deprecated
     */
    public function getTimestampHex(): string
    {
        return $this->uuid()->getTimestampHex();
    }

    /**
     * @deprecated
     */
    public function getVariant(): int|null
    {
        return $this->uuid()->getVariant();
    }

    /**
     * @deprecated
     */
    public function getVersion(): int|null
    {
        return $this->uuid()->getVersion();
    }

    /**
     * @deprecated
     */
    public function serialize(): never
    {
        throw new \LogicException('Serializes with __serialize Magic Method');
    }

    /**
     * @deprecated
     */
    public function unserialize($data): never
    {
        throw new \LogicException('Deserializes with __unserialize Magic Method');
    }
}
