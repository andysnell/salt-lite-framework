<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier;

use GuzzleHttp\Client as GuzzleClient;
use Maknz\Slack\Client;
use PhoneBurner\SaltLite\Framework\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Framework\Configuration\Configuration;
use PhoneBurner\SaltLite\Framework\Container\MutableContainer;
use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Notifier\Slack\SlackApiNotificationClient;
use PhoneBurner\SaltLite\Framework\Notifier\Slack\SlackNotificationClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class NotifierServiceProvider implements ServiceProvider
{
    public function register(MutableContainer $container): void
    {
        $container->set(
            SlackNotificationClient::class,
            static function (ContainerInterface $container): SlackNotificationClient {
                $config = $container->get(Configuration::class)->get('notifier.slack');
                $logger = $container->get(LoggerInterface::class);
            //                if ($container->get(BuildStage::class) !== BuildStage::Production) {
            //                    return new NullSlackNotificationClient(
            //                        $config['default_options']['channel'] ?? 'developers',
            //                    );
            //                }

                return new SlackApiNotificationClient(
                    new Client($config['endpoint'], $config['default_options'] ?? [], new GuzzleClient()),
                    $container->get(LockFactory::class),
                    $logger,
                );
            },
        );
    }
}
