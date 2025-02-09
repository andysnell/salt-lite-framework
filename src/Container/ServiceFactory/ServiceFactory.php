<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceFactory;

use PhoneBurner\SaltLite\Framework\App\App;

interface ServiceFactory
{
    public function __invoke(App $app): object;
}
