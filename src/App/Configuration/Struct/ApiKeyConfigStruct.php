<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Configuration\Struct;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NonEmptyOrNull;

/**
 * General purpose configuration struct for something like an API key, enforcing
 * either a non-empty string or null value.
 */
abstract readonly class ApiKeyConfigStruct implements ConfigStruct
{
    /**
     * @var non-empty-string|null
     */
    public string|null $api_key;

    public function __construct(string|null $api_key)
    {
        $this->api_key = NonEmptyOrNull::string($api_key);
    }

    public function __serialize(): array
    {
        return [$this->api_key];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct($data[0] ?? null);
    }
}
