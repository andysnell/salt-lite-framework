<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;

#[DefaultServiceProvider(AppServiceProvider::class)]
enum Context
{
    case Http;
    case Cli;
    case Test;
}
