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
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyDispatcherInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

class ConsoleApplicationServiceFactory implements ServiceFactory
{
    private const string APP_NAME = "Salt-Lite Command Line Console";

    public function __invoke(App $app, string $id): ConsoleApplication
    {
        $configuration = $app->config->get('database.doctrine.connections.default.migrations') ?? [];

        $application = new ConsoleApplication();
        $application->setName(self::APP_NAME);
        $application->setDispatcher($app->get(SymfonyDispatcherInterface::class));
        $application->setCommandLoader($app->get(CommandLoaderInterface::class));
        $application->setAutoExit(false); // allow the CliKernel to handle exit
        $application->setCatchExceptions($app->environment->context !== Context::Http);
        $application->setCatchErrors(false);

        OrmConsoleRunner::addCommands($application, $app->get(EntityManagerProvider::class));
        MigrationConsoleRunner::addCommands($application, DependencyFactory::fromConnection(
            new ConfigurationArray($configuration),
            ghost(fn(ExistingConnection $ghost): null => $ghost->__construct($app->get(Connection::class))),
            $app->get(LoggerInterface::class),
        ));

        return $application;
    }
}
