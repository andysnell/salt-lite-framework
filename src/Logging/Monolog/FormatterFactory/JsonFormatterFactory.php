<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Monolog\FormatterFactory;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologFormatterFactory;

class JsonFormatterFactory implements MonologFormatterFactory
{
    public function make(LoggingHandlerConfigStruct $config): FormatterInterface
    {
        return new JsonFormatter(
            $config->formatter_options['batch_mode'] ?? JsonFormatter::BATCH_MODE_NEWLINES,
            $config->formatter_options['append_new_line'] ?? true,
            $config->formatter_options['ignore_empty_context_and_extra'] ?? false,
            $config->formatter_options['include_stacktraces'] ?? false,
        );
    }
}
