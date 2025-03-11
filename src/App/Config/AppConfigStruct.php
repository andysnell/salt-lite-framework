<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Config;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Framework\Domain\I18n\IsoLocale;
use PhoneBurner\SaltLite\Framework\Domain\Time\TimeZone\Tz;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;

class AppConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;

    public function __construct(
        public string $name,
        #[\SensitiveParameter] public SharedKey $key,
        public Tz $timezone = Tz::Utc,
        public IsoLocale $locale = IsoLocale::EN_US,
    ) {
    }

    public function __serialize(): array
    {
        return [
            $this->name,
            $this->key->export(),
            $this->timezone,
            $this->locale,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct(
            $data[0],
            SharedKey::import($data[1]),
            $data[2],
            $data[3],
        );
    }
}
