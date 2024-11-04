<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Ip;

enum IpAddressType
{
    case IPv4;
    case IPv6;
}
