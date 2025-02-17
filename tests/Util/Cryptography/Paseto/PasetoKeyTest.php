<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidStringLength;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Paseto\PasetoKey;
use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NonEmpty;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class PasetoKeyTest extends TestCase
{
    #[TestWith(['3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6'])]
    #[TestWith(['zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'])]
    #[TestWith(['Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'])]
    #[Test]
    public function make_derives_symmetric_and_asymmetric_keys_from_string_longer_than_key_bytes(string $key): void
    {
        $key = PasetoKey::fromSeed($key);

        $shared = $key->shared()->bytes();
        $secret = $key->secret()->bytes();
        $public = $key->public()->bytes();
        $id = $key->id();

        // 32 bytes * 2 for hex encoding + 7 characters for "sha256:"
        self::assertSame(71, \strlen($id));
        self::assertStringStartsWith('sha256:', $id);

        // Make sure we aren't accidentally using the id as a key
        $binary_id = \hex2bin(\substr($key->id(), 8));
        self::assertNotSame($binary_id, $shared);
        self::assertNotSame($binary_id, $public);
        self::assertNotSame($binary_id, $secret);

        // Make sure the keys are all different
        self::assertNotSame($shared, $secret);
        self::assertNotSame($shared, $public);
        self::assertNotSame($public, $secret);

        self::assertSame(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES, \strlen($shared));
        self::assertSame(\SODIUM_CRYPTO_SIGN_SECRETKEYBYTES, \strlen($secret));
        self::assertSame(\SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES, \strlen($public));
        self::assertSame($public, \sodium_crypto_sign_publickey_from_secretkey($secret));
    }

    #[TestWith(['3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6'])]
    #[TestWith(['zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz'])]
    #[TestWith(['Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'])]
    #[Test]
    public function constructor_checks_key_length(string $key): void
    {
        $this->expectException(InvalidStringLength::class);
        new PasetoKey(NonEmpty::string($key));
    }

    #[TestWith(['3f09f3b08a4c50631b725da2397b4f4e'])]
    #[Test]
    public function make_derives_symmetric_and_asymmetric_keys_from_key_length_string(string $key): void
    {
        $key = PasetoKey::fromSeed($key);

        $shared = $key->shared()->bytes();
        $secret = $key->secret()->bytes();
        $public = $key->public()->bytes();
        $id = $key->id();

        // 32 bytes * 2 for hex encoding + 7 characters for "sha256:"
        self::assertSame(71, \strlen($id));
        self::assertStringStartsWith('sha256:', $id);

        // Make sure we aren't accidentally using the id as a key
        $binary_id = \hex2bin(\substr($key->id(), 8));
        self::assertNotSame($binary_id, $shared);
        self::assertNotSame($binary_id, $public);
        self::assertNotSame($binary_id, $secret);

        // Make sure the keys are all different
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
        $this->expectException(InvalidStringLength::class);
        PasetoKey::fromSeed($key);
    }
}
