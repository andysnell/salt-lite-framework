<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Email;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface NullableEmailAddressAware
{
    public function getEmailAddress(): EmailAddress|null;
}
