<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Config;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Logging\LogLevel;

final readonly class LoggingHandlerConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param class-string<HandlerInterface> $handler_class
     * @param class-string<FormatterInterface>|null $formatter_class
     */
    public function __construct(
        public string $handler_class,
        public array $handler_options = [],
        public string|null $formatter_class = null,
        public array $formatter_options = [],
        public LogLevel $level = LogLevel::Debug,
        public bool $bubble = true,
    ) {
    }
}
