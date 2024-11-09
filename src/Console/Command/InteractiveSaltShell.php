<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\AppendOnlyCache;
use PhoneBurner\SaltLite\Framework\Cache\Cache;
use PhoneBurner\SaltLite\Framework\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psy\Configuration as PsyConfiguration;
use Psy\Shell;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(self::NAME, self::DESCRIPTION)]
class InteractiveSaltShell extends Command
{
    public const string NAME = 'shell';

    public const string DESCRIPTION = 'Interactive PHP REPL Shell (PsySH)';

    public const string MESSAGE = "Interactive PHP REPL Shell (PsySH) \r\nEnter \"ls -l\" to List Defined Variables or \"exit\" to Quit";

    public const array DEFAULT_SERVICES = [
        'config' => Configuration::class,
        'container' => MutableContainer::class,
        'environment' => Environment::class,
        'event_dispatcher' => EventDispatcherInterface::class,
        'logger' => LoggerInterface::class,
        'em' => EntityManagerInterface::class,
        'connection' => Connection::class,
        'redis' => \Redis::class,
        'cache' => Cache::class,
        'append_only_cache' => AppendOnlyCache::class,
        'lock_factory' => LockFactory::class,
        'storage' => FilesystemOperator::class,
        'bus' => MessageBusInterface::class,
        'mailer' => MailerInterface::class,
    ];

    private const array DEFAULT_CONFIG = [
        'startupMessage' => self::MESSAGE,
        'updateCheck' => 'never',
    ];

    public function __construct(private readonly MutableContainer $container)
    {
        parent::__construct(self::NAME);
        $this->setDescription(self::DESCRIPTION);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->container->get(Configuration::class)->get('shell') ?? [];
        $shell_config = new PsyConfiguration($config['psysh_config'] ?? self::DEFAULT_CONFIG);

        $shell = new Shell($shell_config);

        $shell->setScopeVariables(\array_map(
            $this->container->get(...),
            \array_merge(self::DEFAULT_SERVICES, $config['services'] ?? []),
        ));

        foreach ($config['imports'] ?? [] as $import) {
            $shell->addCode(\sprintf('use %s;', $import), true);
        }

        $shell->setIncludes();
        return $shell->run();
    }
}
