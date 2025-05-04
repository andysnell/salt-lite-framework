<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Exception\InvalidHandlerConfiguration;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologHandlerFactory;

class RotatingFileHandlerFactory implements MonologHandlerFactory
{
    public function __construct(private readonly MonologFormatterFactory $formatters)
    {
    }

    public function make(LoggingHandlerConfigStruct $config): HandlerInterface
    {
        \assert($config->handler_class === RotatingFileHandler::class);
        return new RotatingFileHandler(
            $config->handler_options['filename'] ?? throw new InvalidHandlerConfiguration('Missing Rotating File Handler Filename'),
            $config->handler_options['max_files'] ?? 7,
            Level::from($config->level->toMonlogLogLevel()),
            $config->bubble,
            $config->handler_options['file_permission'] ?? null,
            $config->handler_options['use_locking'] ?? false,
            $config->handler_options['date_format'] ?? RotatingFileHandler::FILE_PER_DAY,
            $config->handler_options['filename_format'] ?? '{filename}-{date}',
        )->setFormatter($this->formatters->make($config));
    }
}
