<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Fixtures;

use PhoneBurner\SaltLite\Framework\Util\Enum\WithStringBackedInstanceStaticMethod;
use PhoneBurner\SaltLite\Framework\Util\Enum\WithValuesStaticMethod;

enum StoplightState: string
{
    use WithStringBackedInstanceStaticMethod;
    use WithValuesStaticMethod;

    case Red = 'red';
    case Yellow = 'yellow';
    case Green = 'green';
}
