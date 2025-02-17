<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Symmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\Aes256Gcm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\XChaCha20Blake2b;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\XChaCha20Poly1305;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\XSalsa20Poly1305;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AlgorithmTest extends TestCase
{
    #[Test]
    public function happy_path_for_implementation(): void
    {
        self::assertInstanceOf(XChaCha20Blake2b::class, Algorithm::XChaCha20Blake2b->implementation());
        self::assertInstanceOf(XChaCha20Poly1305::class, Algorithm::XChaCha20Poly1305->implementation());
        self::assertInstanceOf(XSalsa20Poly1305::class, Algorithm::XSalsa20Poly1305->implementation());
        self::assertInstanceOf(Aes256Gcm::class, Algorithm::Aes256Gcm->implementation());
    }
}
