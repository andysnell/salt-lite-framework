<?php

declare(strict_types=1);

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\Logging\LoggingHandlerConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PhoneNumberProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PsrMessageInterfaceProcessor;
use Psr\Log\LogLevel;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;
use function PhoneBurner\SaltLite\Framework\stage;

return [
    'logging' => [
        // Set the channel name to be used by the default logger, this should normally
        // be set to the application name in kabob-case, which is the fallback, if
        // the channel is not set or null. This identifies the source of the log
        // entry among other applications when aggregated in a tool like Loggly.
        'channel' => env('SALT_PSR3_LOG_CHANNEL'),
        'processors' => [
            PsrLogMessageProcessor::class,
            PsrMessageInterfaceProcessor::class,
            PhoneNumberProcessor::class,
            EnvironmentProcessor::class,
            LogTraceProcessor::class,
        ],
        // Configure Handlers By Build Stage
        // @see \PhoneBurner\SaltLite\Framework\Logging\LoggerServiceFactory
        'handlers' => stage(
            [
                new LoggingHandlerConfigStruct(
                    handler_class: LogglyHandler::class,
                    handler_options: [
                        'token' => env('SALT_LOGGLY_TOKEN'),
                        'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::INFO),
                        'bubble' => true,
                    ],
                    formatter_class: LogglyFormatter::class,
                ),
                new LoggingHandlerConfigStruct(
                    handler_class: SlackWebhookHandler::class,
                    handler_options: [
                        'webhook_url' => env('SALT_SLACK_WEBHOOK_URL'),
                        'channel' => env('SALT_SLACK_DEFAULT_CHANNEL'),
                        'level' => LogLevel::CRITICAL,
                        'bubble' => true,
                    ],
                    formatter_class: LogglyFormatter::class,
                ),
            ],
            [
                new LoggingHandlerConfigStruct(
                    handler_class: StreamHandler::class,
                    handler_options: [
                        'stream' => \sys_get_temp_dir() . '/salt-lite/salt-lite.log',
                        'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::DEBUG),
                        'bubble' => true,
                    ],
                    formatter_class: JsonFormatter::class,
                ),
                new LoggingHandlerConfigStruct(
                    handler_class: RotatingFileHandler::class,
                    handler_options: [
                        'filename' => path('/storage/logs/salt-lite.log'),
                        'max_files' => 7,
                        'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::DEBUG),
                        'bubble' => true,
                    ],
                    formatter_class: JsonFormatter::class,
                ),
            ],
        ),
    ],
];
