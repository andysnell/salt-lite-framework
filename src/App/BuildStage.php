<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\Util\Enum\WithStringBackedInstanceStaticMethod;

enum BuildStage: string
{
    use WithStringBackedInstanceStaticMethod;

    case Production = 'production';
    case Integration = 'integration';
    case Development = 'development';
}
