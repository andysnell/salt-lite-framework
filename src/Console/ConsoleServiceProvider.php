<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console;

use Crell\AttributeUtils\ClassAnalyzer;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\App\Command\DebugAppKeysCommand;
use PhoneBurner\SaltLite\Framework\Console\Command\InteractiveSaltShellCommand;
use PhoneBurner\SaltLite\Framework\Console\EventListener\ConsoleErrorListener;
use PhoneBurner\SaltLite\Framework\EventDispatcher\Command\DebugEventListenersCommand;
use PhoneBurner\SaltLite\Framework\Http\Routing\Command\CacheRoutesCommand;
use PhoneBurner\SaltLite\Framework\Http\Routing\Command\ListRoutesCommand;
use PhoneBurner\SaltLite\Framework\Scheduler\Command\ConsumeScheduledMessagesCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
        InteractiveSaltShellCommand::class,
        ListRoutesCommand::class,
        CacheRoutesCommand::class,
        DebugAppKeysCommand::class,
        DebugEventListenersCommand::class,
        ConsumeMessagesCommand::class,
        ConsumeScheduledMessagesCommand::class,
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
            ConsoleApplication::class,
            InteractiveSaltShellCommand::class,
            ConsoleErrorListener::class,
        ];
    }

    public static function bind(): array
    {
        return [
            Application::class => ConsoleApplication::class,
        ];
    }

    #[\Override]
    public static function register(App $app): void
    {
        $app->set(
            CliKernel::class,
            static fn(App $app): CliKernel => new CliKernel(
                $app->get(ConsoleApplication::class),
                $app->get(EventDispatcherInterface::class),
            ),
        );

        $app->set(
            CommandLoaderInterface::class,
            static fn(App $app): CommandLoader => new CommandLoader($app->services, $app->get(ClassAnalyzer::class), [
                ...self::FRAMEWORK_COMMANDS,
                ...($app->config->get('console.commands') ?? []),
            ]),
        );

        $app->set(
            ConsoleErrorListener::class,
            static fn(App $app): ConsoleErrorListener => new ConsoleErrorListener($app->get(LoggerInterface::class)),
        );

        $app->set(ConsoleApplication::class, new ConsoleApplicationServiceFactory());

        $app->set(
            InteractiveSaltShellCommand::class,
            static fn(App $app): InteractiveSaltShellCommand => new InteractiveSaltShellCommand($app),
        );
    }
}
