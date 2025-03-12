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
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\App\Context;
use PhoneBurner\SaltLite\Container\ServiceFactory;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineConfigStruct;
use PhoneBurner\SaltLite\Framework\Database\Config\DoctrineConnectionConfigStruct;
use PhoneBurner\SaltLite\Type\Type;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyDispatcherInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

class ConsoleApplicationServiceFactory implements ServiceFactory
{
    private const string APP_NAME = "Salt-Lite Command Line Console";

    public function __invoke(App $app, string $id): ConsoleApplication
    {
        $doctrine_config = Type::of(DoctrineConfigStruct::class, $app->config->get('database.doctrine'));
        $default_connection = $doctrine_config->connections[$doctrine_config->default_connection];
        \assert($default_connection instanceof DoctrineConnectionConfigStruct);

        $application = new ConsoleApplication();
        $application->setName(self::APP_NAME);
        $application->setDispatcher($app->get(SymfonyDispatcherInterface::class));
        $application->setCommandLoader($app->get(CommandLoaderInterface::class));
        $application->setAutoExit(false); // allow the CliKernel to handle exit
        $application->setCatchExceptions($app->environment->context !== Context::Http);
        $application->setCatchErrors(false);

        OrmConsoleRunner::addCommands($application, $app->get(EntityManagerProvider::class));
        MigrationConsoleRunner::addCommands($application, DependencyFactory::fromConnection(
            new ConfigurationArray([
                'table_storage' => $default_connection->migrations->table_storage,
                'migrations_paths' => $default_connection->migrations->migrations_paths,
            ]),
            ghost(fn(ExistingConnection $ghost): null => $ghost->__construct($app->get(Connection::class))),
            $app->get(LoggerInterface::class),
        ));

        return $application;
    }
}
