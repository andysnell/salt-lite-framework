<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Event;

use PhoneBurner\SaltLite\Framework\App\App;

final readonly class ApplicationBootstrap
{
    public function __construct(public App $app)
    {
    }
}
