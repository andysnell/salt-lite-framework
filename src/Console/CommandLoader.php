<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class CommandLoader implements CommandLoaderInterface
{
    private readonly array $command_map;

    public function __construct(
        private readonly ContainerInterface $container,
        array $commands,
    ) {
        $command_map = [];
        foreach ($commands as $command) {
            if (! \is_a($command, Command::class, true)) {
                throw new \InvalidArgumentException(\sprintf('Command "%s" must be an instance of "%s".', $command, Command::class));
            }

            $command_map[$command::getDefaultName()] = $command;
        }

        $this->command_map = $command_map;
    }

    #[\Override]
    public function get(string $name): Command
    {
        if (! $this->has($name)) {
            throw new CommandNotFoundException(\sprintf('Command "%s" does not exist.', $name));
        }

        $command = $this->container->get($this->command_map[$name]);
        \assert($command instanceof Command);

        return $command;
    }

    #[\Override]
    public function has(string $name): bool
    {
        return isset($this->command_map[$name]);
    }

    #[\Override]
    public function getNames(): array
    {
        return \array_keys($this->command_map);
    }
}
