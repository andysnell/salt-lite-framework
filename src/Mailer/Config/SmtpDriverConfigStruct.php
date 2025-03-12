<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

class SmtpDriverConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public function __construct(
        public string $host,
        public int $port,
        public string $user,
        #[\SensitiveParameter] public string $password,
        public bool $encryption = true,
    ) {
    }
}
