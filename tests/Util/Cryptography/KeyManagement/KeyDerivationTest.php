<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\KeyManagement;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\KeyDerivation;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class KeyDerivationTest extends TestCase
{
    #[Test]
    public function happy_path_shared(): void
    {
        $key = SharedKey::generate();

        $dk1 = KeyDerivation::shared($key);
        self::assertNotSame($key->bytes(), $dk1->bytes());

        $dk2 = KeyDerivation::shared($key);
        self::assertSame($dk1->bytes(), $dk2->bytes());

        $dk3 = KeyDerivation::shared($key, 'test');
        self::assertNotSame($dk1->bytes(), $dk3->bytes());

        $dk4 = KeyDerivation::shared($key, 'test');
        self::assertSame($dk3->bytes(), $dk4->bytes());

        $dk5 = KeyDerivation::shared($key, 'test1');
        self::assertNotSame($dk1->bytes(), $dk5->bytes());
    }

    #[Test]
    public function happy_path_encryption_keypair(): void
    {
        $key = SharedKey::generate();

        $dk1 = KeyDerivation::encryption($key);
        self::assertNotSame($key->bytes(), $dk1->bytes());

        $dk2 = KeyDerivation::encryption($key);
        self::assertSame($dk1->bytes(), $dk2->bytes());

        $dk3 = KeyDerivation::encryption($key, 'test');
        self::assertNotSame($dk1->bytes(), $dk3->bytes());

        $dk4 = KeyDerivation::encryption($key, 'test');
        self::assertSame($dk3->bytes(), $dk4->bytes());

        $dk5 = KeyDerivation::encryption($key, 'test1');
        self::assertNotSame($dk1->bytes(), $dk5->bytes());
    }

    #[Test]
    public function happy_path_signature_keypair(): void
    {
        $key = SharedKey::generate();

        $dk1 = KeyDerivation::signature($key);
        self::assertNotSame($key->bytes(), $dk1->bytes());

        $dk2 = KeyDerivation::signature($key);
        self::assertSame($dk1->bytes(), $dk2->bytes());

        $dk3 = KeyDerivation::signature($key, 'test');
        self::assertNotSame($dk1->bytes(), $dk3->bytes());

        $dk4 = KeyDerivation::signature($key, 'test');
        self::assertSame($dk3->bytes(), $dk4->bytes());

        $dk5 = KeyDerivation::signature($key, 'test1');
        self::assertNotSame($dk1->bytes(), $dk5->bytes());
    }

    #[Test]
    public function happy_path_for_hkdf(): void
    {
        $key = SharedKey::generate();

        $dk1 = KeyDerivation::hkdf($key, 32, 'test');
        self::assertNotSame($key->bytes(), $dk1);

        $dk2 = KeyDerivation::hkdf($key, 32, 'test');
        self::assertSame($dk1, $dk2);

        $dk3 = KeyDerivation::hkdf($key, 64, 'test');
        self::assertNotSame($dk1, $dk3);
        self::assertSame(64, \strlen($dk3));
        self::assertSame($dk1, \substr($dk3, 0, 32));

        $dk4 = KeyDerivation::hkdf($key, 32, 'test1');
        self::assertNotSame($dk1, $dk4);
    }
}
