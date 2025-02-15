<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Attribute;

/**
 * This class is internal to the Salt-Lite Framework, and should not be used
 * directly in userland code. No guarantee is made about the ongoing existence
 * or backwards compatibility of this code.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Internal
{
    public function __construct(
        public string $help = '',
    ) {
    }
}
