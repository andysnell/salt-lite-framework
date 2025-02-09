<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck;

use Doctrine\DBAL\Connection;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\Clock\Clock;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\AmqpTransportHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\MySqlHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\PhpRuntimeHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\RedisHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\RequestHandler\HealthCheckRequestHandler;
use PhoneBurner\SaltLite\Framework\HealthCheck\RequestHandler\ReadyCheckRequestHandler;
use PhoneBurner\SaltLite\Framework\HealthCheck\Service\AppHealthCheckBuilder;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class HealthCheckServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            HealthCheckBuilder::class,
            HealthCheckRequestHandler::class,
            ReadyCheckRequestHandler::class,
            AmqpTransportHealthCheckService::class,
            MySqlHealthCheckService::class,
            PhpRuntimeHealthCheckService::class,
            RedisHealthCheckService::class,
        ];
    }

    public static function bind(): array
    {
        return [];
    }

    #[\Override]
    public static function register(App $app): void
    {
         $app->set(
             HealthCheckBuilder::class,
             static fn(App $app): HealthCheckBuilder => new AppHealthCheckBuilder(
                 $app->get(Clock::class),
                 $app->get(LoggerInterface::class),
                 \array_map($app->services->get(...), $app->config->get('health_check.services') ?: []),
                 \trim($app->config->get('app.name') . ' API Health Check'),
             ),
         );

         $app->set(
             HealthCheckRequestHandler::class,
             static fn(App $app): HealthCheckRequestHandler => new HealthCheckRequestHandler(
                 $app->get(HealthCheckBuilder::class),
                 $app->get(LoggerInterface::class),
             ),
         );

         $app->set(
             ReadyCheckRequestHandler::class,
             static fn(App $app): ReadyCheckRequestHandler => new ReadyCheckRequestHandler(
                 $app->get(LoggerInterface::class),
             ),
         );

         $app->set(
             AmqpTransportHealthCheckService::class,
             static fn(App $app): AmqpTransportHealthCheckService => new AmqpTransportHealthCheckService(
                 $app->get(AmqpTransport::class),
                 $app->get(LogTrace::class),
                 $app->get(LoggerInterface::class),
             ),
         );

         $app->set(
             MySqlHealthCheckService::class,
             static fn(App $app): MySqlHealthCheckService => new MySqlHealthCheckService(
                 $app->get(Connection::class),
                 $app->get(LogTrace::class),
                 $app->get(LoggerInterface::class),
             ),
         );

        $app->set(
            PhpRuntimeHealthCheckService::class,
            static fn(App $app): PhpRuntimeHealthCheckService => new PhpRuntimeHealthCheckService(
                $app->get(LogTrace::class),
            ),
        );

        $app->set(
            RedisHealthCheckService::class,
            static fn(App $app): RedisHealthCheckService => new RedisHealthCheckService(
                $app->get(Redis::class),
                $app->get(LogTrace::class),
                $app->get(LoggerInterface::class),
            ),
        );
    }
}
