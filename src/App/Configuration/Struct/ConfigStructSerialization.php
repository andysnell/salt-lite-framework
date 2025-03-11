<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Configuration\Struct;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;

/**
 * @phpstan-require-implements ConfigStruct
 */
trait ConfigStructSerialization
{
    public function __serialize(): array
    {
        return \get_object_vars($this);
    }

    public function __unserialize(array $data): void
    {
        $this->__construct(...$data);
    }
}
