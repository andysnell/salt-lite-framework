<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\Command\CommandHelper;

use PhoneBurner\SaltLite\Framework\Http\Routing\Command\CommandHelper\RouteDefinitionListFormatter;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\DefinitionList;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\HashAlgorithm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class HashFormatter extends RouteDefinitionListFormatter
{
    public const string FORMAT = 'hash';

    #[\Override]
    public function render(
        OutputInterface $output,
        DefinitionList $definitions,
        bool $show_attributes = true,
        bool $show_namespaces = true,
    ): int {
        $output->writeln(\hash(HashAlgorithm::SHA256->value, \json_encode([...$definitions], \JSON_THROW_ON_ERROR)));
        return Command::SUCCESS;
    }
}
