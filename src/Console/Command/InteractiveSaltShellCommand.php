<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console\Command;

use Carbon\CarbonImmutable;
use Crell\AttributeUtils\ClassAnalyzer;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Cache\AppendOnlyCache;
use PhoneBurner\SaltLite\Cache\Cache;
use PhoneBurner\SaltLite\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Configuration\Configuration;
use PhoneBurner\SaltLite\Container\MutableContainer;
use PhoneBurner\SaltLite\Container\ServiceContainer;
use PhoneBurner\SaltLite\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\SaltLite\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\SaltLite\Cryptography\ConstantTime;
use PhoneBurner\SaltLite\Cryptography\Hash\HashAlgorithm;
use PhoneBurner\SaltLite\Cryptography\Hash\Hmac;
use PhoneBurner\SaltLite\Cryptography\Natrium;
use PhoneBurner\SaltLite\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Cryptography\Symmetric\Symmetric;
use PhoneBurner\SaltLite\Domain\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\SaltLite\Domain\PhoneNumber\DomesticPhoneNumber;
use PhoneBurner\SaltLite\Domain\PhoneNumber\E164;
use PhoneBurner\SaltLite\Enum\Enum;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Http\Psr7;
use PhoneBurner\SaltLite\Iterator\Arr;
use PhoneBurner\SaltLite\Iterator\Iter;
use PhoneBurner\SaltLite\Math\Math;
use PhoneBurner\SaltLite\MessageBus\MessageBus;
use PhoneBurner\SaltLite\String\Encoding\Encoder;
use PhoneBurner\SaltLite\String\Encoding\Encoding;
use PhoneBurner\SaltLite\String\Str;
use PhoneBurner\SaltLite\Time\Ttl;
use PhoneBurner\SaltLite\Type\Cast\NullableCast;
use PhoneBurner\SaltLite\Type\Func;
use PhoneBurner\SaltLite\Type\Reflect;
use PhoneBurner\SaltLite\Type\Type;
use PhoneBurner\SaltLite\Uuid\Uuid;
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
class InteractiveSaltShellCommand extends Command
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
        'natrium' => Natrium::class,
        'mailer' => MailerInterface::class,
        'message_bus' => MessageBus::class,
        'redis_manager' => RedisManager::class,
        'storage' => FilesystemOperator::class,

    ];

    /**
     * Note, if a service has the same basename as a PHP function, the aliasing
     * will override that function name. E.g. `Hash::class` is problematic because it
     * conflicts with the built-in `hash()` function.
     */
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
        SharedKey::class,
        Ciphertext::class,
        EncryptionKeyPair::class,
        SignatureKeyPair::class,
        Natrium::class,
        Encoding::class,
        Encoder::class,
        ConstantTime::class,
        HashAlgorithm::class,
        Hmac::class,
        Symmetric::class,
        Asymmetric::class,
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
