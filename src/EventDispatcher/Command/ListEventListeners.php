<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\Command;

use PhoneBurner\SaltLite\Framework\EventDispatcher\LazyListener;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[AsCommand(self::NAME, self::DESCRIPTION)]
class ListEventListeners extends Command
{
    public const string NAME = 'event-dispatcher:list';

    public const string DESCRIPTION = 'List the event listeners defined in "includes/events.php"';

    public function __construct(private readonly EventDispatcher $event_dispatcher)
    {
        parent::__construct(self::NAME);
        $this->setDescription(self::DESCRIPTION);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Registered Events and Listeners</info>');

        foreach ($this->event_dispatcher->getListeners() as $event => $listeners) {
            foreach (Arr::wrap($listeners) as $listener) {
                $listener_name = $listener::class;
                if ($listener instanceof LazyListener) {
                    $listener_name = $listener->listener_class;
                    if ($listener->listener_method) {
                        $listener_name .= '::' . $listener->listener_method;
                    }
                }

                $output->writeln(\sprintf("<comment>%s</comment>:  %s", $event, $listener_name));
            }
        }

        return Command::SUCCESS;
    }
}
