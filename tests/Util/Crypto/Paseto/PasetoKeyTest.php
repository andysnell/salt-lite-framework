<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\PasetoKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class PasetoKeyTest extends TestCase
{
    #[TestWith(['3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6'])]
    #[TestWith(['zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'])]
    #[TestWith(['Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'])]
    #[Test]
    public function it_derives_symmetric_and_asymmetric_keys_from_string(string $key): void
    {
        $key = PasetoKey::make($key);

        $shared = $key->shared();
        $secret = $key->secret();
        $public = $key->public();

        self::assertNotSame($shared, $secret);
        self::assertNotSame($shared, $public);
        self::assertNotSame($public, $secret);
        self::assertSame(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES, \strlen($shared));
        self::assertSame(\SODIUM_CRYPTO_SIGN_SECRETKEYBYTES, \strlen($secret));
        self::assertSame(\SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES, \strlen($public));
        self::assertSame($public, \sodium_crypto_sign_publickey_from_secretkey($secret));
    }

    #[TestWith([''])]
    #[TestWith(['3f09f3b08a4c50631b725da2397b4f4'])]
    #[Test]
    public function short_keys_result_in_thrown_exception(string $key): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Key Length');
        PasetoKey::make($key);
    }
}
