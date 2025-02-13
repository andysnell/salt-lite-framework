<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Processor\PsrLogMessageProcessor;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\EnvironmentProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\LogTraceProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PhoneNumberProcessor;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PsrMessageInterfaceProcessor;
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

        $app->set(PsrLogMessageProcessor::class, new NewInstanceServiceFactory());

        $app->set(PhoneNumberProcessor::class, new NewInstanceServiceFactory());

        $app->set(PsrMessageInterfaceProcessor::class, new NewInstanceServiceFactory());

        $app->set(EnvironmentProcessor::class, new NewInstanceServiceFactory(args: [$app->environment]));

        // LogTrace is eagerly instantiated in the AppServiceProvider, so we can
        // safely pass it as an argument to the service factory, instead of wrapping
        // the instantiation in a closure.
        $app->set(LogTraceProcessor::class, new NewInstanceServiceFactory(args: [$app->get(LogTrace::class)]));
    }
}
