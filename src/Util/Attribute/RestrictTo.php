<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class RestrictTo
{
    /**
     * @var array<class-string>
     */
    public readonly array $classes;

    /**
     * @param class-string ...$classes
     */
    public function __construct(string ...$classes)
    {
        $this->classes = $classes;
    }
}
