<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Memory;

final readonly class Bytes
{
    public function __construct(public int $value)
    {
        $value >= 0 || throw new \UnexpectedValueException('Bytes must be non-negative integer');
    }

    public function bits(): int
    {
        return $this->value * 8;
    }
}
