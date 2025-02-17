<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\Key;

interface KeyPair extends Key
{
    public function secret(): SecretKey;

    public function public(): PublicKey;

    public static function generate(): static;

    public static function fromSeed(string $seed): static;
}
