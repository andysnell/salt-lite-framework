<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Monolog;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;

interface MonologHandlerFactory
{
    public const string DEFAULT_FORMATTER = LineFormatter::class;

    public function make(LoggingHandlerConfigStruct $config): HandlerInterface;
}
