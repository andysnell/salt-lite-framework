<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface NullableAreaCodeAware
{
    public function getAreaCode(): AreaCode|null;
}
