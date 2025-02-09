<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console\Command;

use Carbon\CarbonImmutable;
use Crell\AttributeUtils\ClassAnalyzer;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Cache\AppendOnlyCache;
use PhoneBurner\SaltLite\Framework\Cache\Cache;
use PhoneBurner\SaltLite\Framework\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\DomesticPhoneNumber;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\E164;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\MessageBus\MessageBus;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NullableCast;
use PhoneBurner\SaltLite\Framework\Util\Helper\Enum;
use PhoneBurner\SaltLite\Framework\Util\Helper\Func;
use PhoneBurner\SaltLite\Framework\Util\Helper\Iter;
use PhoneBurner\SaltLite\Framework\Util\Helper\Math;
use PhoneBurner\SaltLite\Framework\Util\Helper\Psr7;
use PhoneBurner\SaltLite\Framework\Util\Helper\Reflect;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use PhoneBurner\SaltLite\Framework\Util\Helper\Type;
use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psy\Configuration as PsyConfiguration;
use Psy\Shell;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(self::NAME, self::DESCRIPTION)]
class InteractiveSaltShell extends Command
{
    public const string NAME = 'shell';

    public const string DESCRIPTION = 'Interactive PHP REPL Shell (PsySH)';

    public const string MESSAGE = "Interactive PHP REPL Shell (PsySH) \r\nEnter \"ls -l\" to List Defined Variables or \"exit\" to Quit";

    public const array DEFAULT_SERVICES = [
        'app' => App::class,
        'append_only_cache' => AppendOnlyCache::class,
        'cache' => Cache::class,
        'class_analyzer' => ClassAnalyzer::class,
        'config' => Configuration::class,
        'connection' => Connection::class,
        'container' => ServiceContainer::class,
        'em' => EntityManagerInterface::class,
        'environment' => Environment::class,
        'event_dispatcher' => EventDispatcherInterface::class,
        'lock_factory' => LockFactory::class,
        'logger' => LoggerInterface::class,
        'mailer' => MailerInterface::class,
        'message_bus' => MessageBus::class,
        'redis' => \Redis::class,
        'storage' => FilesystemOperator::class,

    ];

    public const array DEFAULT_IMPORTS = [
        AreaCode::class,
        Arr::class,
        CarbonImmutable::class,
        DomesticPhoneNumber::class,
        E164::class,
        Reflect::class,
        Str::class,
        Ttl::class,
        Uuid::class,
        Enum::class,
        Func::class,
        Iter::class,
        Math::class,
        Psr7::class,
        Type::class,
        NullableCast::class,
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
        $config = $this->container->get(Configuration::class)->get('console.shell') ?? [];
        $shell_config = new PsyConfiguration($config['psysh'] ?? self::DEFAULT_CONFIG);

        $shell = new Shell($shell_config);

        $services = \array_unique(\array_merge(self::DEFAULT_SERVICES, $config['services'] ?? []));
        $shell->setScopeVariables(\array_map($this->container->get(...), $services));

        foreach (\array_unique(\array_merge(self::DEFAULT_IMPORTS, $config['services'] ?? [])) as $import) {
            $shell->addCode(\sprintf('use %s;', $import), true);
        }

        $shell->setIncludes();
        return $shell->run();
    }
}
