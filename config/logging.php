<?php

declare(strict_types=1);

use Monolog\Handler\LogglyHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use Psr\Log\LogLevel;

use function PhoneBurner\SaltLite\Framework\env;
use function PhoneBurner\SaltLite\Framework\path;
use function PhoneBurner\SaltLite\Framework\stage;

return [
    'logging' => [
        'processors' => [
            PsrLogMessageProcessor::class,
            EnvironmentProcessor::class,
            LogTraceProcessor::class,
        ],
        'handlers' => stage(
            [LogglyHandler::class],
            [RotatingFileHandler::class],
            [StreamHandler::class],
        ),
        RotatingFileHandler::class => [
            'path' => path('/storage/logs/salt-lite.log'),
            'max_files' => 7,
            'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::INFO, LogLevel::DEBUG),
            'bubble' => true,
        ],
        StreamHandler::class => [
            'stream' => 'php://stdout',
            'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::INFO, LogLevel::DEBUG),
            'bubble' => true,
        ],
        LogglyHandler::class => [
            'token' => env('SALT_LOGGLY_TOKEN'),
            'level' => env('SALT_PSR3_LOG_LEVEL', LogLevel::INFO, LogLevel::DEBUG),
            'bubble' => true,
        ],
    ],
];
