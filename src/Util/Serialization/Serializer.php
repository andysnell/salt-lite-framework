<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Serialization;

use PhoneBurner\SaltLite\Framework\Util\Enum\WithUnitEnumInstanceStaticMethod;

enum Serializer
{
    use WithUnitEnumInstanceStaticMethod;

    case Igbinary;
    case Php;
}
