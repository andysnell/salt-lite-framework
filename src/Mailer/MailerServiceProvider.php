<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Domain\Email\EmailAddress;
use PhoneBurner\SaltLite\Framework\Mailer\Transport\TransportServiceFactory;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Command\MailerTestCommand;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Messenger\MessageHandler;
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

        $app->set(TransportInterface::class, new TransportServiceFactory());

        $app->set(
            MailerTestCommand::class,
            static fn(App $app): MailerTestCommand => new MailerTestCommand(
                $app->get(TransportInterface::class),
            ),
        );
    }
}
