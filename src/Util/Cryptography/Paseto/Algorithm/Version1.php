<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Paseto\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Paseto\Exception\PasetoCryptoException;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Paseto\PasetoKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Paseto\PasetoMessage;

class Version1
{
    public function encrypt(PasetoKey $key, PasetoMessage $message): string
    {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }

    public function decrypt(PasetoKey $key, string $token): PasetoMessage
    {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }

    public function sign(PasetoKey $key, PasetoMessage $message): string
    {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }

    public function verify(PasetoKey $key, string $token, string $implicit): PasetoMessage
    {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }
}
