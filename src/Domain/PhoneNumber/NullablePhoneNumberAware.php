<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface NullablePhoneNumberAware
{
    public function getPhoneNumber(): NullablePhoneNumber;
}
