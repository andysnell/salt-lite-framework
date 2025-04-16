<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier\Slack\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

final readonly class SlackWebhookNotifierConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public function __construct(
        public string $endpoint = '',
        public array $options = [],
    ) {
    }
}
