<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto\Symmetric;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\SerializationProhibited;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SharedKeyTest extends TestCase
{
    #[Test]
    public function key_cannot_be_serialized(): void
    {
        $key = SharedKey::generate();

        $this->expectException(SerializationProhibited::class);
        \serialize($key);
    }
}
