<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Attribute;

/**
 * This class is internal to the Salt-Lite Framework, and should not be used
 * directly in userland code. No guarantee is made about the ongoing existence
 * or backwards compatibility of this code. There will usually be some kind of
 * higher-level abstraction that should be used instead, that will provide a
 * more stable API that is less likely to change.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_METHOD)]
final class Internal
{
    public function __construct(
        public string $help = '',
        public string $abstraction = '',
    ) {
    }
}
