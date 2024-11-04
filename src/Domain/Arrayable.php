<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain;

interface Arrayable
{
    public function toArray(): array;
}
