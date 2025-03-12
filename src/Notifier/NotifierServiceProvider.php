<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier;

use GuzzleHttp\Client as GuzzleClient;
use Maknz\Slack\Client;
use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\App\BuildStage;
use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Container\DeferrableServiceProvider;
use PhoneBurner\SaltLite\Framework\Notifier\Slack\NullSlackNotificationClient;
use PhoneBurner\SaltLite\Framework\Notifier\Slack\SlackNotificationClient;
use PhoneBurner\SaltLite\Framework\Notifier\Slack\SlackWebhookNotificationClient;
use Psr\Log\LoggerInterface;

use function PhoneBurner\SaltLite\Framework\ghost;

/**
 * @codeCoverageIgnore
 */
#[Internal('Override Definitions in Application Service Providers')]
final class NotifierServiceProvider implements DeferrableServiceProvider
{
    public static function provides(): array
    {
        return [
            SlackNotificationClient::class,
        ];
    }

    public static function bind(): array
    {
        return [];
    }

    public static function register(App $app): void
    {
        $app->set(
            SlackNotificationClient::class,
            static function (App $app): SlackNotificationClient {
                $config = $app->config->get('notifier.slack_webhooks');
                return match ($app->environment->stage) {
                    BuildStage::Production => ghost(static fn(SlackWebhookNotificationClient $ghost): null => $ghost->__construct(
                        new Client($config['endpoint'], $config['default_options'] ?? [], new GuzzleClient()),
                        $app->services->get(LockFactory::class),
                        $app->services->get(LoggerInterface::class),
                    )),
                    default => new NullSlackNotificationClient(
                        $app->services->get(LoggerInterface::class),
                        $config['default_options']['channel'] ?? 'developers',
                    ),
                };
            },
        );
    }
}
