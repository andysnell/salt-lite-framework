<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\FormatterFactory\ContainerFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologHandlerFactory;

class TestHandlerFactory implements MonologHandlerFactory
{
    public function __construct(private readonly ContainerFormatterFactory $formatters)
    {
    }

    public function make(LoggingHandlerConfigStruct $config): HandlerInterface
    {
        return new TestHandler(
            Level::from($config->level->toMonlogLogLevel()),
            $config->bubble,
        )->setFormatter($this->formatters->make($config));
    }
}
