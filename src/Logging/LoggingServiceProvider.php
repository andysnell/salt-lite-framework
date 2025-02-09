<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogglyFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\LogglyHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Psr\Log\LoggerInterface;

use function PhoneBurner\SaltLite\Framework\ghost;
use function PhoneBurner\SaltLite\Framework\path;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class LoggingServiceProvider implements ServiceProvider
{
    public static function bind(): array
    {
        return [];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            LoggerInterface::class,
            ghost(static function (Logger $logger) use ($app): void {
                /** @var list<HandlerInterface> $handlers */
                $handlers = \array_map($app->services->get(...), $app->config->get('logging.handlers') ?? []);
                /** @var list<ProcessorInterface> $processors */
                $processors = \array_map($app->services->get(...), $app->config->get('logging.processors') ?? []);

                $logger->__construct(
                    Str::kabob($app->config->get('app.name')),
                    $handlers,
                    $processors,
                );

                // On resolution, replace the resolved logger as the container's
                // logger instance, which should also consume any buffered log
                // entries from the default buffer logger.
                $app->services->setLogger($logger);
            }),
        );

        $app->set(
            PsrLogMessageProcessor::class,
            static fn(App $app): PsrLogMessageProcessor => new PsrLogMessageProcessor(),
        );

        $app->set(
            EnvironmentProcessor::class,
            static fn(App $app): EnvironmentProcessor => new EnvironmentProcessor(
                $app->environment,
            ),
        );

        $app->set(
            LogTraceProcessor::class,
            static fn(App $app): LogTraceProcessor => new LogTraceProcessor(
                $app->get(LogTrace::class),
            ),
        );

        $app->set(
            RotatingFileHandler::class,
            static function (App $app): RotatingFileHandler {
                $config = $app->config->get('logging.' . RotatingFileHandler::class) ?? [];
                $handler = new RotatingFileHandler(
                    $config['path'] ?? path('/storage/logs/salt-lite.log'),
                    $config['max_files'] ?? 7,
                    $config['level'] ?? LogLevel::Debug->value,
                    (bool)($config['bubble'] ?? true),
                );

                $handler->setFormatter(new LineFormatter());

                return $handler;
            },
        );

        $app->set(
            StreamHandler::class,
            static function (App $app): StreamHandler {
                $config = $app->config->get('logging.' . StreamHandler::class) ?? [];
                $handler = new StreamHandler(
                    $config['path'] ?? path('/storage/logs/salt-lite.log'),
                    $config['level'] ?? LogLevel::Debug->value,
                    (bool)($config['bubble'] ?? true),
                );

                $handler->setFormatter(new LineFormatter());

                return $handler;
            },
        );

        $app->set(
            LogglyHandler::class,
            static function (App $app): LogglyHandler {
                $config = $app->config->get('logging.' . LogglyHandler::class) ?? [];
                $handler = new LogglyHandler(
                    (string)($config['token'] ?? null),
                    $config['level'] ?? LogLevel::Debug->value,
                    (bool)($config['bubble'] ?? true),
                );

                $handler->setFormatter(new LogglyFormatter());

                return $handler;
            },
        );
    }
}
