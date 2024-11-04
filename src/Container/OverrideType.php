<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

enum OverrideType
{
    case Position;
    case Name;
    case Hint;
}
