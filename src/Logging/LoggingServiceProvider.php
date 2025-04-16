<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Container\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\SaltLite\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\Config\LoggingConfigStruct;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\FormatterFactory\ContainerFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\FormatterFactory\JsonFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\FormatterFactory\LineFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\FormatterFactory\LogglyFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\ContainerHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\ErrorLogHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\LogglyHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\NoopHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\NullHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\RotatingFileHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\SlackWebhookHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\StreamHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\HandlerFactory\TestHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologFormatterFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologHandlerFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\MonologLoggerServiceFactory;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PhoneNumberProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PsrMessageInterfaceProcessor;
use PhoneBurner\SaltLite\Logging\LogTrace;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class LoggingServiceProvider implements ServiceProvider
{
    public static function bind(): array
    {
        return [
            LoggerServiceFactory::class => MonologLoggerServiceFactory::class,
            MonologFormatterFactory::class => ContainerFormatterFactory::class,
            MonologHandlerFactory::class => ContainerHandlerFactory::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(LoggerInterface::class, $app->get(LoggerServiceFactory::class));

        $app->set(
            MonologLoggerServiceFactory::class,
            static fn(App $app): MonologLoggerServiceFactory => new MonologLoggerServiceFactory(
                $app->get(MonologHandlerFactory::class),
            ),
        );

        $app->set(PsrLogMessageProcessor::class, NewInstanceServiceFactory::singleton());

        $app->set(PhoneNumberProcessor::class, NewInstanceServiceFactory::singleton());

        $app->set(PsrMessageInterfaceProcessor::class, NewInstanceServiceFactory::singleton());

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
            ContainerFormatterFactory::class,
            static fn(App $app): ContainerFormatterFactory => new ContainerFormatterFactory(
                $app->services,
                $app->get(LoggingConfigStruct::class)->formatter_factories,
            ),
        );

        $app->set(JsonFormatterFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(LineFormatterFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(LogglyFormatterFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(
            ContainerHandlerFactory::class,
            static fn(App $app): ContainerHandlerFactory => new ContainerHandlerFactory(
                $app->services,
                $app->get(LoggingConfigStruct::class)->handler_factories,
            ),
        );

        $app->set(
            ErrorLogHandlerFactory::class,
            static fn(App $app): ErrorLogHandlerFactory => new ErrorLogHandlerFactory(
                $app->get(MonologFormatterFactory::class),
            ),
        );

        $app->set(
            LogglyHandlerFactory::class,
            static fn(App $app): LogglyHandlerFactory => new LogglyHandlerFactory(
                $app->get(MonologFormatterFactory::class),
            ),
        );

        $app->set(
            RotatingFileHandlerFactory::class,
            static fn(App $app): RotatingFileHandlerFactory => new RotatingFileHandlerFactory(
                $app->get(MonologFormatterFactory::class),
            ),
        );

        $app->set(
            SlackWebhookHandlerFactory::class,
            static fn(App $app): SlackWebhookHandlerFactory => new SlackWebhookHandlerFactory(
                $app->get(MonologFormatterFactory::class),
            ),
        );

        $app->set(
            StreamHandlerFactory::class,
            static fn(App $app): StreamHandlerFactory => new StreamHandlerFactory(
                $app->get(MonologFormatterFactory::class),
            ),
        );

        $app->set(TestHandlerFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(NoopHandlerFactory::class, NewInstanceServiceFactory::singleton());

        $app->set(NullHandlerFactory::class, NewInstanceServiceFactory::singleton());
    }
}
