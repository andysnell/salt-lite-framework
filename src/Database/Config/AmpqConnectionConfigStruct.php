<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

final readonly class AmpqConnectionConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public function __construct(
        public string $host,
        public int $port,
        public string $user,
        #[\SensitiveParameter] public string $password,
    ) {
        \assert($port > 0 && $port <= 65535);
        \assert($host !== '');
        \assert($user !== '');
        \assert($password !== '');
    }
}
