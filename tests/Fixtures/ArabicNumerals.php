<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Fixtures;

use PhoneBurner\SaltLite\Framework\Util\Enum\WithIntegerBackedInstanceStaticMethod;
use PhoneBurner\SaltLite\Framework\Util\Enum\WithValuesStaticMethod;

enum ArabicNumerals: int
{
    use WithIntegerBackedInstanceStaticMethod;
    use WithValuesStaticMethod;

    case Zero = 0;
    case One = 1;
    case Two = 2;
    case Three = 3;
    case Four = 4;
    case Five = 5;
    case Six = 6;
    case Seven = 7;
    case Eight = 8;
    case Nine = 9;
}
