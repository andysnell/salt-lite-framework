<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Configuration;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface Configuration
{
    /**
     * Gets a configuration value by key (dot notation),
     * returning null if no value is set.
     */
    public function get(string $key): mixed;
}
