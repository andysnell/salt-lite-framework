<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

class DatabaseConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public function __construct(
        public readonly AmpqConfigStruct|null $ampq = null,
        public readonly RedisConfigStruct|null $redis = null,
        public readonly DoctrineConfigStruct|null $doctrine = null,
    ) {
    }
}
