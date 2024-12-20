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
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function PhoneBurner\SaltLite\Framework\path;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class LoggingServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->set(
            LoggerInterface::class,
            static function (ContainerInterface $container): LoggerInterface {
                $config = $container->get(Configuration::class);

                /** @var list<HandlerInterface> $handlers */
                $handlers = \array_map($container->get(...), $config->get('logging.handlers') ?? []);

                /** @var list<ProcessorInterface> $processors */
                $processors = \array_map($container->get(...), $config->get('logging.processors') ?? []);

                return new Logger(
                    Str::kabob($config->get('app.name')),
                    $handlers,
                    $processors,
                );
            },
        );

        $container->set(
            PsrLogMessageProcessor::class,
            static function (ContainerInterface $container): PsrLogMessageProcessor {
                return new PsrLogMessageProcessor();
            },
        );

        $container->set(
            EnvironmentProcessor::class,
            static function (ContainerInterface $container): EnvironmentProcessor {
                return new EnvironmentProcessor($container->get(Environment::class));
            },
        );

        $container->set(
            LogTraceProcessor::class,
            static function (ContainerInterface $container): LogTraceProcessor {
                return new LogTraceProcessor($container->get(LogTrace::class));
            },
        );

        $container->set(RotatingFileHandler::class, static function (ContainerInterface $container): RotatingFileHandler {
            $config = $container->get(Configuration::class)->get('logging.' . RotatingFileHandler::class) ?? [];
            $handler = new RotatingFileHandler(
                $config['path'] ?? path('/storage/logs/salt-lite.log'),
                $config['max_files'] ?? 7,
                $config['level'] ?? LogLevel::DEBUG,
                (bool)($config['bubble'] ?? true),
            );

            $handler->setFormatter(new LineFormatter());

            return $handler;
        });

        $container->set(
            StreamHandler::class,
            static function (ContainerInterface $container): StreamHandler {
                $config = $container->get(Configuration::class)->get('logging.' . StreamHandler::class) ?? [];
                $handler = new StreamHandler(
                    $config['path'] ?? path('/storage/logs/salt-lite.log'),
                    $config['level'] ?? LogLevel::DEBUG,
                    (bool)($config['bubble'] ?? true),
                );

                $handler->setFormatter(new LineFormatter());

                return $handler;
            },
        );

        $container->set(
            LogglyHandler::class,
            static function (ContainerInterface $container): LogglyHandler {
                $config = $container->get(Configuration::class)->get('logging.' . LogglyHandler::class) ?? [];
                $handler = new LogglyHandler(
                    (string)($config['token'] ?? null),
                    $config['level'] ?? LogLevel::DEBUG,
                    (bool)($config['bubble'] ?? true),
                );

                $handler->setFormatter(new LogglyFormatter());

                return $handler;
            },
        );
    }
}
