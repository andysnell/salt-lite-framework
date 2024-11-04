<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Hash;

interface MessageDigest extends \Stringable
{
    public function algorithm(): HashAlgorithm;

    public function digest(): string;
}
