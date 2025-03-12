<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\SaltLite\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Cryptography\Symmetric\SymmetricAlgorithm;
use PhoneBurner\SaltLite\I18n\IsoLocale;
use PhoneBurner\SaltLite\Time\TimeZone\Tz;

class AppConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;

    public function __construct(
        public string $name,
        #[\SensitiveParameter] public SharedKey $key,
        public Tz $timezone = Tz::Utc,
        public IsoLocale $locale = IsoLocale::EN_US,
        public SymmetricAlgorithm $symmetric_algorithm = SymmetricAlgorithm::Aegis256,
        public AsymmetricAlgorithm $asymmetric_algorithm = AsymmetricAlgorithm::X25519Aegis256,
    ) {
    }

    public function __serialize(): array
    {
        return [
            $this->name,
            $this->key->export(),
            $this->timezone,
            $this->locale,
            $this->symmetric_algorithm,
            $this->asymmetric_algorithm,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct(
            $data[0],
            SharedKey::import($data[1]),
            $data[2],
            $data[3],
            $data[4],
            $data[5],
        );
    }
}
