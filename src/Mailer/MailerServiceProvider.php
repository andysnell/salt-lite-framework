<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Domain\Email\EmailAddress;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Command\MailerTestCommand;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Messenger\MessageHandler;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class MailerServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            Mailer::class,
            MailerInterface::class,
            TransportInterface::class,
            MessageHandler::class,
            MailerTestCommand::class,
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
            Mailer::class,
            ghost(static fn(SymfonyMailerAdapter $ghost): null => $ghost->__construct(
                $app->get(MailerInterface::class),
                new EmailAddress($app->config->get('mailer.default_from_address')),
            )),
        );

        $app->set(
            MailerInterface::class,
            static fn(App $app): SymfonyMailer => match ((bool)$app->config->get('mailer.async')) {
                true => ghost(static fn(SymfonyMailer $ghost): null => $ghost->__construct(
                    $app->get(TransportInterface::class),
                    $app->get(MessageBusInterface::class),
                    $app->get(EventDispatcherInterface::class),
                )),
                false => ghost(static fn(SymfonyMailer $ghost): null => $ghost->__construct(
                    $app->get(TransportInterface::class),
                )),
            },
        );

        $app->set(
            MessageHandler::class,
            ghost(static fn(MessageHandler $ghost): null => $ghost->__construct(
                $app->get(TransportInterface::class),
            )),
        );

        $app->set(
            TransportInterface::class,
            static function (App $app): TransportInterface {
                $transport_driver = (string)$app->config->get('mailer.default_driver');
                $transport_config = $app->config->get('mailer.drivers.' . $transport_driver) ?? [];
                \assert(\is_array($transport_config));

                $dns = match (TransportDriver::tryFrom($transport_driver)) {
                    TransportDriver::SendGrid => \vsprintf('sendgrid+api://%s@default', [
                        $transport_config['api_key'] ?? throw new \RuntimeException('Missing SendGrid API key'),
                    ]),
                    TransportDriver::Smtp => \vsprintf('smtp://%s:%s@%s:%s%s', [
                        $transport_config['user'] ?? throw new \RuntimeException('Missing SMTP Credentials'),
                        \urlencode($transport_config['pass'] ?? throw new \RuntimeException('Missing SMTP Credentials')),
                        $transport_config['host'] ?? throw new \RuntimeException('Missing SMTP Credentials'),
                        $transport_config['port'] ?? throw new \RuntimeException('Missing SMTP Credentials'),
                        $transport_config['encryption'] ? '' : '?auto_tls=false',
                    ]),
                    TransportDriver::None => 'null://default',
                    default => throw new \RuntimeException("Unknown mailer transport driver: {$transport_driver}"),
                };

                return Transport::fromDsn(
                    dsn: $dns,
                    dispatcher: $app->get(EventDispatcherInterface::class),
                    logger: $app->get(LoggerInterface::class),
                );
            },
        );

        $app->set(
            MailerTestCommand::class,
            static fn(App $app): MailerTestCommand => new MailerTestCommand(
                $app->get(TransportInterface::class),
            ),
        );
    }
}
