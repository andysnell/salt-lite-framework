<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\AppKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\CryptoRuntimeException;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\SerializationProhibited;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class AppKeyTest extends TestCase
{
    #[Test]
    public function app_key_happy_path(): void
    {
        $key = AppKey::generate();

        self::assertSame(AppKey::LENGTH, \strlen($key->bytes()));

        self::assertSame($key->bytes(), new AppKey($key->bytes())->bytes());
        self::assertEquals($key, new AppKey($key->bytes()));

        $encoded = $key->encoded();
        self::assertTrue(\str_starts_with($encoded, 'base64:'));
        self::assertSame($key->bytes(), new AppKey($encoded)->bytes());

        self::assertSame('sha256:' . \hash('sha256', $key->bytes()), $key->id());
    }

    #[Test]
    #[TestWith([AppKey::LENGTH - 1])]
    #[TestWith([AppKey::LENGTH + 1])]
    public function app_key_with_invalid_length(int $length): void
    {
        \assert($length > 0 && $length < \PHP_INT_MAX);
        $invalid_key = \random_bytes($length);

        $this->expectException(CryptoRuntimeException::class);
        new AppKey($invalid_key);
    }

    #[Test]
    public function key_cannot_be_serialized(): void
    {
        $key = AppKey::generate();

        $this->expectException(SerializationProhibited::class);
        \serialize($key);
    }
}
