<?php

declare(strict_types=1);

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
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
            EnvironmentProcessor::class,
            LogTraceProcessor::class,
        ],
        // Configure Handlers By Build Stage
        // @see \PhoneBurner\SaltLite\Framework\Logging\LoggerServiceFactory
        'handlers' => stage(
            [
                [
                    'handler_class' => LogglyHandler::class,
                    'handler_options' => [
                        'token' => env('SALT_LOGGLY_TOKEN'),
                        'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::INFO),
                        'bubble' => true,
                    ],
                    'formatter_class' => LogglyFormatter::class,
                    'formatter_options' => [],
                ],
                [
                    'handler_class' => SlackWebhookHandler::class,
                    'handler_options' => [
                        'webhook_url' => env('SALT_SLACK_WEBHOOK_URL'),
                        'channel' => env('SALT_SLACK_DEFAULT_CHANNEL'),
                        'level' => LogLevel::CRITICAL,
                        'bubble' => true,
                    ],
                    'formatter_class' => LineFormatter::class,
                    'formatter_options' => [],
                ],
            ],
            [
                [
                    'handler_class' => RotatingFileHandler::class,
                    'handler_options' => [
                        'filename' => path('/storage/logs/salt-lite.log'),
                        'max_files' => 7,
                        'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::DEBUG),
                        'bubble' => true,
                    ],
                    'formatter_class' => LineFormatter::class,
                    'formatter_options' => [],
                ],
            ],
        ),
    ],
];
