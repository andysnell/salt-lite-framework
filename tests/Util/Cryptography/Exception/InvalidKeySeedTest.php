<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Exception;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidKeySeed;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidKeySeedTest extends TestCase
{
    #[Test]
    public function happy_path_test_length(): void
    {
        self::assertSame('Key Seed Must Be Exactly 16 Bytes', InvalidKeySeed::length(16)->getMessage());
    }
}
