<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\Exception;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
class InvalidPhoneNumber extends \RuntimeException
{
}
