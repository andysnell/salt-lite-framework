<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Internal
{
    public function __construct(
        public string $help = '',
    ) {
    }
}
