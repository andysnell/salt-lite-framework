<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\AppKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class AppKeyTest extends TestCase
{
    #[Test]
    public function app_key_happy_path(): void
    {
        $key = AppKey::generate();

        self::assertSame(AppKey::LENGTH, \strlen($key->value));

        self::assertSame($key->value, (new AppKey($key->value))->value);
        self::assertSame($key->value, (string)new AppKey($key->value));

        $encoded = $key->encoded();
        self::assertTrue(\str_starts_with($encoded, 'base64:'));
        self::assertSame($key->value, (new AppKey($encoded))->value);
    }

    #[Test]
    #[TestWith([AppKey::LENGTH - 1])]
    #[TestWith([AppKey::LENGTH + 1])]
    public function app_key_with_invalid_length(int $length): void
    {
        \assert($length > 0 && $length < \PHP_INT_MAX);
        $invalid_key = \random_bytes($length);

        $this->expectException(\InvalidArgumentException::class);
        new AppKey($invalid_key);
    }
}
