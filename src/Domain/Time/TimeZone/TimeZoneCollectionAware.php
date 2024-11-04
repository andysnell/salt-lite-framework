<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone;

interface TimeZoneCollectionAware
{
    public function getTimeZones(): TimeZoneCollection;
}
