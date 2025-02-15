<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode;

use PhoneBurner\SaltLite\Framework\Domain\PhpSerializable;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\TimeZoneCollection;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\TimeZoneCollectionAware;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\TimeZoneFactory;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

/**
 * @implements PhpSerializable<array{npa: int<200,999>}>
 */
#[Contract]
final readonly class AreaCode implements
    \Stringable,
    AreaCodeAware,
    TimeZoneCollectionAware,
    PhpSerializable
{
    /**
     * Numbering Plan Area (NPA) Code for this Area Code.
     *
     * @var int<200,999>
     */
    public int $npa;

    /**
     * @var int-mask-of<AreaCodeStatus::*>
     */
    public int $status;

    public AreaCodePurpose $purpose;

    public TimeZoneCollection $time_zones;

    public AreaCodeLocation $location;

    private function __construct(int $npa)
    {
        if ($npa < 200 || $npa > 999) {
            throw new \UnexpectedValueException('Invalid Area Code NPA Value');
        }

        $metadata = AreaCodeData::METADATA[$npa];

        $this->npa = $npa;
        $this->status = AreaCodeStatus::mask($metadata);
        $this->purpose = AreaCodePurpose::tryFrom($metadata >> 8 & 0xFF) ?? AreaCodePurpose::GeneralPurpose;
        $this->time_zones = ($metadata & AreaCodeStatus::ASSIGNABLE)
            ? TimeZoneFactory::collect(...AreaCodeData::TIME_ZONE_MAP[$metadata >> 16 & 0xFF])
            : TimeZoneFactory::collect();
        $this->location = AreaCodeLocation::make(...AreaCodeData::LOCATION_MAP[$metadata >> 24 & 0xFF]);
    }

    public static function make(AreaCodeAware|int|string $area_code): self
    {
        static $cache = [];
        return $area_code instanceof AreaCodeAware
            ? $area_code->getAreaCode()
            : $cache[(int)$area_code] ??= new self((int)$area_code);
    }

    public static function tryFrom(mixed $area_code): self|null
    {
        try {
            return $area_code !== null ? self::make($area_code) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function all(): AreaCodeCollection
    {
        return new AreaCodeCollection(...\array_map([self::class, 'make'], \range(200, 999)));
    }

    public static function active(): AreaCodeCollection
    {
        return self::all()->filter(static fn(self $area_code): bool => $area_code->isActive());
    }

    #[\Override]
    public function getAreaCode(): self
    {
        return $this;
    }

    #[\Override]
    public function getTimeZones(): TimeZoneCollection
    {
        return $this->time_zones;
    }

    /**
     * We consider area codes that are in the process of activating, e.g. a
     * new overlay that may start issuing numbers at some point in time after
     * it is scheduled, to also be "active" to account for delays in updates
     * to libphonenumber and the upstream NANP database not tracking an actual
     * "go live" date for each area code.
     */
    public function isActive(): bool
    {
        return $this->status & AreaCodeStatus::ACTIVE
            || $this->status === (AreaCodeStatus::ASSIGNABLE | AreaCodeStatus::ASSIGNED | AreaCodeStatus::SCHEDULED);
    }

    #[\Override]
    public function __toString(): string
    {
        return (string)$this->npa;
    }

    #[\Override]
    public function __serialize(): array
    {
        return ['npa' => $this->npa];
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->__construct($data['npa']);
    }
}
