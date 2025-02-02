<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto;

interface Key
{
    /**
     * @return string The raw binary "key material" string
     */
    public function bytes(): string;

    public static function length(): int;
}
