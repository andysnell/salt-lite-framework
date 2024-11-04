<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

enum BuildStage: string
{
    case Production = 'production';
    case Integration = 'integration';
    case Development = 'development';
}
