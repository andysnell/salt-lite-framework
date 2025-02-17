<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Attribute;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Poly1305;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Attribute\AlgorithmImplementation;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\XChaCha20Blake2b;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AlgorithmImplementationTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $symmetric = new XChaCha20Blake2b();
        $sut = new AlgorithmImplementation($symmetric);
        self::assertSame($symmetric, $sut->algorithm);

        $asymmetric = new X25519XChaCha20Poly1305();
        $sut = new AlgorithmImplementation($asymmetric);
        self::assertSame($asymmetric, $sut->algorithm);
    }
}
