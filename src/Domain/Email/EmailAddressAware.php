<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Email;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface EmailAddressAware
{
    public function getEmailAddress(): EmailAddress;
}
