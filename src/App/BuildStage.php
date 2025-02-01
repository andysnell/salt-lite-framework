<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Enum\WithStringBackedInstanceStaticMethod;

#[DefaultServiceProvider(AppServiceProvider::class)]
enum BuildStage: string
{
    use WithStringBackedInstanceStaticMethod;

    case Production = 'production';
    case Integration = 'integration';
    case Development = 'development';
}
