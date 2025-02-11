<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Log\LoggerInterface;

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
        $app->set(LoggerInterface::class, new LoggerServiceFactory());

        $app->set(
            PsrLogMessageProcessor::class,
            static fn(App $app): PsrLogMessageProcessor => new PsrLogMessageProcessor(),
        );

        $app->set(
            EnvironmentProcessor::class,
            static fn(App $app): EnvironmentProcessor => new EnvironmentProcessor($app->environment),
        );

        $app->set(
            LogTraceProcessor::class,
            static fn(App $app): LogTraceProcessor => new LogTraceProcessor($app->get(LogTrace::class)),
        );
    }
}
