<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

enum Context
{
    case Http;
    case Cli;
    case Test;
}
