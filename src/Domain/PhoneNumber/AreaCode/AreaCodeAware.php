<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode;

interface AreaCodeAware extends NullableAreaCodeAware
{
    public function getAreaCode(): AreaCode;
}
