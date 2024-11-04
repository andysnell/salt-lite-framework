<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture;

class LazyObject
{
    public function __construct(private readonly \Closure $initializer)
    {
    }

    public function call(): mixed
    {
        return ($this->initializer)();
    }
}
