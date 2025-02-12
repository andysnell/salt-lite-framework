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
use PhoneBurner\SaltLite\Framework\Console\Command\InteractiveSaltShell;
use PhoneBurner\SaltLite\Framework\Console\EventListener\ConsoleErrorListener;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\SaltLite\Framework\EventDispatcher\Command\DebugEventListeners;
use PhoneBurner\SaltLite\Framework\Http\Routing\Command\CacheRoutes;
use PhoneBurner\SaltLite\Framework\Http\Routing\Command\ListRoutes;
use PhoneBurner\SaltLite\Framework\Scheduler\Command\ConsumeScheduleMessages;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Mailer\Command\MailerTestCommand;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\Command\StatsCommand;
use Symfony\Component\Messenger\Command\StopWorkersCommand;
use Symfony\Component\Scheduler\Command\DebugCommand as ScheduleDebugCommand;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class ConsoleServiceProvider implements DeferrableServiceProvider
{
    public const array FRAMEWORK_COMMANDS = [
        InteractiveSaltShell::class,
        ListRoutes::class,
        CacheRoutes::class,
        DebugEventListeners::class,
        ConsumeMessagesCommand::class,
        ConsumeScheduleMessages::class,
        StatsCommand::class,
        StopWorkersCommand::class,
        ScheduleDebugCommand::class,
        MailerTestCommand::class,
    ];

    public static function provides(): array
    {
        return [
            CliKernel::class,
            CommandLoaderInterface::class,
            Application::class,
            ConsoleApplicationFactory::class,
            InteractiveSaltShell::class,
            ConsoleErrorListener::class,
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
            CliKernel::class,
            static fn(App $app): CliKernel => new CliKernel($app->get(Application::class)),
        );

        $app->set(
            CommandLoaderInterface::class,
            static fn(App $app): CommandLoader => new CommandLoader($app->services, [
                ...self::FRAMEWORK_COMMANDS,
                ...($app->config->get('console.commands') ?? []),
            ]),
        );

        $app->set(
            ConsoleErrorListener::class,
            static fn(App $app): ConsoleErrorListener => new ConsoleErrorListener($app->get(LoggerInterface::class)),
        );

        $app->set(Application::class, static function (App $app): Application {
            $configuration = $app->config->get('database.doctrine.connections.default.migrations') ?? [];

            $dependency_factory = DependencyFactory::fromConnection(
                new ConfigurationArray($configuration),
                new ExistingConnection($app->get(Connection::class)),
                $app->get(LoggerInterface::class),
            );

            $application = $app->get(ConsoleApplicationFactory::class)->make();
            MigrationConsoleRunner::addCommands($application, $dependency_factory);
            OrmConsoleRunner::addCommands($application, $app->get(EntityManagerProvider::class));

            $application->setCommandLoader($app->get(CommandLoaderInterface::class));
            $application->setAutoExit($app->environment->context !== Context::Http);
            $application->setCatchExceptions($app->environment->context !== Context::Http);

            return $application;
        });

        $app->set(ConsoleApplicationFactory::class, new NewInstanceServiceFactory(ConsoleApplicationFactory::class));

        $app->set(InteractiveSaltShell::class, new NewInstanceServiceFactory(InteractiveSaltShell::class, [$app]));
    }
}
