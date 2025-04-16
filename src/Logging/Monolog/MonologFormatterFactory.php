<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Monolog;

use Monolog\Formatter\FormatterInterface;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;

interface MonologFormatterFactory
{
    public function make(LoggingHandlerConfigStruct $config): FormatterInterface;
}
