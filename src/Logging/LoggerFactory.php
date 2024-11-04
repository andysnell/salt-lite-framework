<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use Psr\Log\LoggerInterface;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

class LoggerFactory
{
    public function make(
        Environment $environment,
        LogTrace $log_trace,
    ): LoggerInterface {
        $logger = new Logger('salt-lite', processors: [
            new PsrLogMessageProcessor(removeUsedContextFields: true),
            new EnvironmentProcessor($environment),
            new LogTraceProcessor($log_trace),
        ]);

        // Log to Docker Logs Via PHP Error Log
        $handler = new ErrorLogHandler();
        $handler->setFormatter(new LineFormatter('%channel%.%level_name%: %message% %context% %extra%'));
        $logger->pushHandler($handler);

        $handler = new RotatingFileHandler(APP_ROOT . '/storage/logs/salt-lite.log', 7);
        $logger->pushHandler($handler);

        return $logger;
    }
}
