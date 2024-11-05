<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

interface NullablePhoneNumberAware
{
    public function getPhoneNumber(): NullablePhoneNumber;
}
