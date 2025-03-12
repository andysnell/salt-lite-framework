<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Config;

use Monolog\Handler\NoopHandler;
use Monolog\Processor\ProcessorInterface;
use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

class LoggingConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param string $channel Set the channel name to be used by the default
     * logger, this should normally be set to the application name in kabob-case,
     * which is the fallback, if the channel is not set or null. This identifies
     * the source of the log entry among other applications when aggregated in a
     * tool like Loggly.
     * @param array<class-string<ProcessorInterface>> $processors
     * @param array<LoggingHandlerConfigStruct> $handlers
     * @param LoggingHandlerConfigStruct $fallback_handler the handler to use
     * when an error is encountered while processing a log entry.
     */
    public function __construct(
        public string|null $channel,
        public array $processors,
        public array $handlers,
        public LoggingHandlerConfigStruct $fallback_handler = new LoggingHandlerConfigStruct(NoopHandler::class),
    ) {
    }
}
