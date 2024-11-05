<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

interface PhoneNumberAware extends NullablePhoneNumberAware
{
    public function getPhoneNumber(): PhoneNumber;
}
