<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519Aes256Gcm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Blake2b;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Poly1305;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XSalsa20Poly1305;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AlgorithmTest extends TestCase
{
    #[Test]
    public function happy_path_for_implementation(): void
    {
        self::assertInstanceOf(X25519XChaCha20Blake2b::class, Algorithm::X25519XChaCha20Blake2b->implementation());
        self::assertInstanceOf(X25519XChaCha20Poly1305::class, Algorithm::X25519XChaCha20Poly1305->implementation());
        self::assertInstanceOf(X25519XSalsa20Poly1305::class, Algorithm::X25519XSalsa20Poly1305->implementation());
        self::assertInstanceOf(X25519Aes256Gcm::class, Algorithm::X25519Aes256Gcm->implementation());
    }
}
