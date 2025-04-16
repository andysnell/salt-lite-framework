<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Domain\Email\EmailAddress;
use PhoneBurner\SaltLite\Framework\Mailer\Transport\TransportDriver;

final readonly class MailerConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param array<value-of<TransportDriver>|string, ConfigStruct> $drivers
     */
    public function __construct(
        public EmailAddress $default_from_address = new EmailAddress('donotreply@example.com'),
        public TransportDriver|string $default_driver = TransportDriver::None,
        public bool $async = false,
        public array $drivers = [],
    ) {
    }
}
