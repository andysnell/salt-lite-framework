<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Command;

use PhoneBurner\SaltLite\Cryptography\KeyManagement\KeyChain;
use PhoneBurner\SaltLite\Cryptography\Natrium;
use PhoneBurner\SaltLite\String\Encoding\Encoding;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(self::NAME, self::DESCRIPTION)]
class DebugAppKeysCommand extends Command
{
    public const string NAME = 'debug:keys';

    public const string DESCRIPTION = 'Display the public keys used by the application.';

    private readonly KeyChain $key_chain;

    public function __construct(Natrium $natrium)
    {
        $this->key_chain = $natrium->keys;
        parent::__construct(self::NAME);
        $this->setDescription(self::DESCRIPTION);
        $this->addOption('secret', description: 'Display the shared (symmetric) App Key, and the secret/public (asymmetric) key pairs for encryption and digital signatures.');
        $this->addOption('encoding', 'e', InputOption::VALUE_REQUIRED, 'The encoding to use for the keys, one of "base64", "base64url", or "hex"', 'base64', [
            'base64',
            'base64url',
            'hex',
        ]);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $encoding = match ($input->getOption('encoding') ?? 'base64') {
            'base64' => Encoding::Base64,
            'base64url' => Encoding::Base64Url,
            'hex' => Encoding::Hex,
            default => throw new \InvalidArgumentException('Invalid encoding.'),
        };

        $io = new SymfonyStyle($input, $output);
        $output->write(\PHP_EOL);
        $input->getOption('secret')
            ? $this->displaySecretKeys($io, $encoding)
            : $this->displayPublicKeys($io, $encoding);

        $output->write(\PHP_EOL);
        return Command::SUCCESS;
    }

    private function displaySecretKeys(OutputInterface $output, Encoding $encoding): void
    {
        $output->writeln('<comment>Shared Keys</comment>');
        $output->writeln(\str_repeat('=', 96));
        $output->writeln('App Key: ' . $this->key_chain->app_key->export($encoding));
        $output->write(\PHP_EOL);

        $output->writeln('<comment>X25519 Encryption Key Pair</comment>');
        $output->writeln(\str_repeat('=', 96));
        $encryption = $this->key_chain->encryption();
        $output->writeln('Secret: ' . $encryption->secret->export($encoding));
        $output->writeln('Public: ' . $encryption->public->export($encoding));
        $output->write(\PHP_EOL);

        $output->writeln('<comment>Ed25519 Signature Key Pair</comment>');
        $output->writeln(\str_repeat('=', 96));
        $signature = $this->key_chain->signature();
        $output->writeln('Secret: ' . $signature->secret->export($encoding));
        $output->writeln('Public: ' . $signature->public->export($encoding));
    }

    private function displayPublicKeys(OutputInterface $output, Encoding $encoding): void
    {
        $output->writeln('<comment>Public Keys</comment>');
        $output->writeln(\str_repeat('=', 80));

        $encryption_public_key = $this->key_chain->encryption()->public->export($encoding);
        $output->writeln('Encryption (X25519): ' . $encryption_public_key);

        $signature_public_key = $this->key_chain->signature()->public->export($encoding);
        $output->writeln('Signature (Ed25519): ' . $signature_public_key);
    }
}
