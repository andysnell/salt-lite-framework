<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory;

use Monolog\Formatter\LogglyFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\LogglyHandler;
use Monolog\Level;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Exception\InvalidHandlerConfiguration;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologHandlerFactory;

class LogglyHandlerFactory implements MonologHandlerFactory
{
    public const string DEFAULT_FORMATTER = LogglyFormatter::class;

    public function __construct(private readonly MonologFormatterFactory $formatters)
    {
    }

    public function make(LoggingHandlerConfigStruct $config): HandlerInterface
    {
        return new LogglyHandler(
            $config->handler_options['token'] ?? throw new InvalidHandlerConfiguration('Missing Loggly API Token'),
            Level::from($config->level->toMonlogLogLevel()),
            $config->bubble,
        )->setFormatter($this->formatters->make($config));
    }
}
