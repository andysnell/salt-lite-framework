<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util;

enum ClassStringType
{
    case Object; // Cannot use "Class" as it is a reserved keyword with extra restrictions
    case Interface;
    case Trait;
    case Enum;
}
