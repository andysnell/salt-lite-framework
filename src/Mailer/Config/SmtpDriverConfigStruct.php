<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer\Config;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;

class SmtpDriverConfigStruct implements ConfigStruct
{
    public function __construct(
        public string $host,
        public int $port,
        public string $user,
        public string $password,
        public bool $encryption = true,
    ) {
    }

    public function __serialize(): array
    {
        return [
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->encryption,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->encryption,
        ] = $data;
    }
}
