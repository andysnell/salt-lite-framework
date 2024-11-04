<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Doctrine\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider as DoctrineEntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\UnknownManagerException;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\ConnectionFactory;
use Psr\Container\ContainerInterface;

class EntityManagerProvider implements DoctrineEntityManagerProvider
{
    public const array KNOWN_MANAGERS = [ConnectionFactory::DEFAULT];

    public function __construct(private readonly ContainerInterface $container)
    {
    }

    #[\Override]
    public function getDefaultManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    #[\Override]
    public function getManager(string $name): EntityManagerInterface
    {
        return match ($name) {
            ConnectionFactory::DEFAULT => $this->container->get(EntityManagerInterface::class),
            default => throw UnknownManagerException::unknownManager($name, self::KNOWN_MANAGERS),
        };
    }
}
