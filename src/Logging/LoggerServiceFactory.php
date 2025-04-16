<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Container\ServiceFactory;
use Psr\Log\LoggerInterface;

interface LoggerServiceFactory extends ServiceFactory
{
    public function __invoke(App $app, string $id): LoggerInterface;
}
