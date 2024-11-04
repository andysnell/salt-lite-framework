<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Marshaller;

enum Serializer
{
    case Igbinary;
    case Php;
}
