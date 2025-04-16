<?php

declare(strict_types=1);

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingHandlerConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PhoneNumberProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PsrMessageInterfaceProcessor;
use PhoneBurner\SaltLite\Logging\LogLevel;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;
use function PhoneBurner\SaltLite\Framework\stage;

return [
    'logging' => new LoggingConfigStruct(
        channel: env('SALT_PSR3_LOG_CHANNEL'),
        processors: [
            PsrMessageInterfaceProcessor::class,
            PhoneNumberProcessor::class,
            EnvironmentProcessor::class,
            LogTraceProcessor::class,
            PsrLogMessageProcessor::class, // must be after any processors that mutate context
        ],
        // Configure Handlers By Build Stage
        handlers: stage(
            [
                new LoggingHandlerConfigStruct(
                    handler_class: LogglyHandler::class,
                    handler_options: [
                        'token' => env('SALT_LOGGLY_TOKEN'),
                    ],
                    formatter_class: LogglyFormatter::class,
                    level: LogLevel::instance(env('SALT_PSR3_LOG_LEVEL', LogLevel::Info)),
                ),
                new LoggingHandlerConfigStruct(
                    handler_class: SlackWebhookHandler::class,
                    handler_options: [
                        'webhook_url' => env('SALT_SLACK_WEBHOOK_URL'),
                        'channel' => env('SALT_SLACK_DEFAULT_CHANNEL'),
                    ],
                    formatter_class: LogglyFormatter::class,
                    level: LogLevel::Critical,
                ),
            ],
            [
                new LoggingHandlerConfigStruct(
                    handler_class: StreamHandler::class,
                    handler_options: [
                        'stream' => \sys_get_temp_dir() . '/salt-lite/salt-lite.log',
                    ],
                    formatter_class: JsonFormatter::class,
                    level: LogLevel::instance(env('SALT_PSR3_LOG_LEVEL', LogLevel::Debug)),
                ),
                new LoggingHandlerConfigStruct(
                    handler_class: RotatingFileHandler::class,
                    handler_options: [
                        'filename' => path('/storage/logs/salt-lite.log'),
                        'max_files' => 7,
                    ],
                    formatter_class: JsonFormatter::class,
                    level: LogLevel::instance(env('SALT_PSR3_LOG_LEVEL', LogLevel::Debug)),
                ),
            ],
        ),
    ),
];
