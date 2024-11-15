<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck;

use Doctrine\DBAL\Connection;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\AmqpTransportHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\MySqlHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\PhpRuntimeHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\ComponentHealthChecks\RedisHealthCheckService;
use PhoneBurner\SaltLite\Framework\HealthCheck\RequestHandler\HealthCheckRequestHandler;
use PhoneBurner\SaltLite\Framework\HealthCheck\Service\AppHealthCheckBuilder;
use PhoneBurner\SaltLite\Framework\Logging\LogTrace;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Clock\Clock;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Redis;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpTransport;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
class HealthCheckServiceProvider implements ServiceProvider
{
    public function register(MutableContainer $container): void
    {
         $container->set(
             HealthCheckBuilder::class,
             static function (MutableContainer $container): HealthCheckBuilder {
                 return new AppHealthCheckBuilder(
                     $container->get(Clock::class),
                     $container->get(LoggerInterface::class),
                     \array_map($container->get(...), $container->get(Configuration::class)->get('app.health_check_services') ?: []),
                     \trim($container->get(Configuration::class)->get('app.name') . ' API Health Check'),
                 );
             },
         );

         $container->set(
             HealthCheckRequestHandler::class,
             static function (MutableContainer $container): HealthCheckRequestHandler {
                    return new HealthCheckRequestHandler(
                        $container->get(HealthCheckBuilder::class),
                    );
             },
         );

         $container->set(
             AmqpTransportHealthCheckService::class,
             static function (ContainerInterface $container): AmqpTransportHealthCheckService {
                return new AmqpTransportHealthCheckService(
                    $container->get(AmqpTransport::class),
                    $container->get(LogTrace::class),
                    $container->get(LoggerInterface::class),
                );
             },
         );

         $container->set(
             MySqlHealthCheckService::class,
             static function (ContainerInterface $container): MySqlHealthCheckService {
                return new MySqlHealthCheckService(
                    $container->get(Connection::class),
                    $container->get(LogTrace::class),
                    $container->get(LoggerInterface::class),
                );
             },
         );

        $container->set(
            PhpRuntimeHealthCheckService::class,
            static function (ContainerInterface $container): PhpRuntimeHealthCheckService {
                return new PhpRuntimeHealthCheckService(
                    $container->get(LogTrace::class),
                );
            },
        );

        $container->set(
            RedisHealthCheckService::class,
            static function (ContainerInterface $container): RedisHealthCheckService {
                return new RedisHealthCheckService(
                    $container->get(Redis::class),
                    $container->get(LogTrace::class),
                    $container->get(LoggerInterface::class),
                );
            },
        );
    }
}
