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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            $application = new class extends Application{
                protected function configureIO(InputInterface $input, OutputInterface $output): void
                {
                    parent::configureIO($input, $output);
                    $output->getFormatter()->setStyle('error', new OutputFormatterStyle('red'));
                }

                protected function doRenderThrowable(\Throwable $e, OutputInterface $output): void
                {
                    $output->getFormatter()->setStyle('error', new OutputFormatterStyle('default', 'red'));
                    parent::doRenderThrowable($e, $output);
                }
            };

            $configuration = $container->get(Configuration::class)->get('database.doctrine.connections.default.migrations') ?? [];

            $dependency_factory = DependencyFactory::fromConnection(
                new ConfigurationArray($configuration),
                new ExistingConnection($container->get(Connection::class)),
                $container->get(LoggerInterface::class),
            );

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
