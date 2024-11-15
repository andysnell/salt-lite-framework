<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationConsoleRunner;
use Doctrine\ORM\Tools\Console\ConsoleRunner as OrmConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;

#[Internal('Override Definitions in Application Service Providers')]
class ConsoleServiceProvider implements ServiceProvider
{
    #[\Override]
    public function register(MutableContainer $container): void
    {
        $container->set(CliKernel::class, static function (MutableContainer $container): CliKernel {
            $application = $container->get(Application::class);
            return new CliKernel($application);
        });

        $container->set(CommandLoaderInterface::class, static function (ContainerInterface $container): CommandLoader {
            return new CommandLoader($container, $container->get(Configuration::class)->get('commands') ?? []);
        });

        $container->set(Application::class, function (MutableContainer $container): Application {
            $configuration = $container->get(Configuration::class)->get('database.doctrine.connections.default.migrations') ?? [];

            $dependency_factory = DependencyFactory::fromConnection(
                new ConfigurationArray($configuration),
                new ExistingConnection($container->get(Connection::class)),
                $container->get(LoggerInterface::class),
            );

            $application = $container->get(ConsoleApplicationFactory::class)->make();
            MigrationConsoleRunner::addCommands($application, $dependency_factory);
            OrmConsoleRunner::addCommands($application, $container->get(EntityManagerProvider::class));

            $context = $container->get(Context::class);

            $application->setCommandLoader($container->get(CommandLoaderInterface::class));
            $application->setAutoExit($context !== Context::Http);
            $application->setCatchExceptions($context !== Context::Http);

            return $application;
        });
    }
}
