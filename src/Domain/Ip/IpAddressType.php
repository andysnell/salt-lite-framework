<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Ip;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
enum IpAddressType
{
    case IPv4;
    case IPv6;
}
