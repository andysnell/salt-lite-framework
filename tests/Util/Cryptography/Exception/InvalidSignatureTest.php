<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Exception;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidSignature;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidSignatureTest extends TestCase
{
    #[Test]
    public function happy_path_test_length(): void
    {
        self::assertSame("Message Signature Must Be Exactly 32 Bytes", InvalidSignature::length(32)->getMessage());
    }
}
