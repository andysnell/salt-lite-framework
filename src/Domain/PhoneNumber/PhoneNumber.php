<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber;

interface PhoneNumber extends NullablePhoneNumber
{
    public function toE164(): E164;
}
