<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto\Hash;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\HashAlgorithm;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HashAlgorithmTest extends TestCase
{
    #[Test]
    public function default_returns_the_default_instance(): void
    {
        self::assertSame(HashAlgorithm::BLAKE2B, HashAlgorithm::default());
        self::assertSame(HashAlgorithm::BLAKE2B, HashAlgorithm::default(true));
        self::assertSame(HashAlgorithm::XXH3, HashAlgorithm::default(false));
    }

    #[Test]
    public function cryptographic_returns_expected_value_for_algo(): void
    {
        self::assertTrue(HashAlgorithm::BLAKE2B->cryptographic());
        self::assertTrue(HashAlgorithm::SHA256->cryptographic());
        self::assertTrue(HashAlgorithm::SHA512_256->cryptographic());

        self::assertFalse(HashAlgorithm::XXH3->cryptographic());
        self::assertFalse(HashAlgorithm::CRC32B->cryptographic());
        self::assertFalse(HashAlgorithm::SHA1->cryptographic());
        self::assertFalse(HashAlgorithm::MD5->cryptographic());
    }

    #[Test]
    public function bytes_returns_expected_value_for_algo(): void
    {
        self::assertSame(32, HashAlgorithm::BLAKE2B->bytes());
        self::assertSame(32, HashAlgorithm::SHA256->bytes());
        self::assertSame(32, HashAlgorithm::SHA512_256->bytes());
        self::assertSame(64, HashAlgorithm::SHA3_512->bytes());

        self::assertSame(8, HashAlgorithm::XXH3->bytes());
        self::assertSame(4, HashAlgorithm::CRC32B->bytes());
        self::assertSame(20, HashAlgorithm::SHA1->bytes());
        self::assertSame(16, HashAlgorithm::MD5->bytes());
    }
}
