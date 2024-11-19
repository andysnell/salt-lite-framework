<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\Exception\PasetoCryptoException;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\Paseto;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\PasetoKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\PasetoMessage;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\PasetoPurpose;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\PasetoVersion;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PasetoTest extends TestCase
{
    #[Test]
    public function v2_local(): void
    {
        $key = PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6');
        $message = PasetoMessage::make([
            'foo' => 42,
        ]);

        $paseto = Paseto::local($key, $message);

        self::assertSame(PasetoVersion::V2, $paseto->version);
        self::assertSame(PasetoPurpose::LOCAL, $paseto->purpose);
        self::assertSame('', $paseto->footer);

        self::assertSame([
            'foo' => 42,
        ], $paseto->decode($key)->getData());
    }

    #[Test]
    public function v2_local_with_footer(): void
    {
        $key = PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6');
        $footer = ['key_id' => 'foobarbaz'];
        $message = PasetoMessage::make([
            'foo' => 42,
        ], $footer);

        $paseto = Paseto::local($key, $message);

        self::assertSame(PasetoVersion::V2, $paseto->version);
        self::assertSame(PasetoPurpose::LOCAL, $paseto->purpose);
        self::assertSame('{"key_id":"foobarbaz"}', $paseto->footer);

        $decoded = $paseto->decode($key);
        self::assertSame(['foo' => 42], $decoded->getData());
        self::assertSame($footer, $decoded->getFooter());
    }

    #[Test]
    public function v2_local_sad_path(): void
    {
        $key = PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6');
        $message = PasetoMessage::make([
            'foo' => 42,
        ]);

        $paseto = Paseto::local($key, $message);

        self::assertSame(PasetoVersion::V2, $paseto->version);
        self::assertSame(PasetoPurpose::LOCAL, $paseto->purpose);
        self::assertSame('', $paseto->footer);

        $this->expectException(PasetoCryptoException::class);
        $paseto->decode(PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f7'));
    }

    #[Test]
    public function v4_public(): void
    {
        $key = PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6');
        $message = PasetoMessage::make([
            'foo' => 42,
        ]);

        $paseto = Paseto::public($key, $message);

        self::assertSame(PasetoVersion::V4, $paseto->version);
        self::assertSame(PasetoPurpose::PUBLIC, $paseto->purpose);
        self::assertSame('', $paseto->footer);

        self::assertSame([
            'foo' => 42,
        ], $paseto->decode($key)->getData());
    }

    #[Test]
    public function v4_public_with_footer(): void
    {
        $key = PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6');
        $footer = ['key_id' => 'foobarbaz'];
        $message = PasetoMessage::make([
            'foo' => 42,
        ], $footer);

        $paseto = Paseto::public($key, $message);

        self::assertSame(PasetoVersion::V4, $paseto->version);
        self::assertSame(PasetoPurpose::PUBLIC, $paseto->purpose);
        self::assertSame('{"key_id":"foobarbaz"}', $paseto->footer);

        $decoded = $paseto->decode($key);
        self::assertSame(['foo' => 42], $decoded->getData());
        self::assertSame($footer, $decoded->getFooter());
    }

    #[Test]
    public function v4_public_sad_path(): void
    {
        $key = PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f6');
        $message = PasetoMessage::make([
            'foo' => 42,
        ]);

        $paseto = Paseto::public($key, $message);

        self::assertSame(PasetoVersion::V4, $paseto->version);
        self::assertSame(PasetoPurpose::PUBLIC, $paseto->purpose);
        self::assertSame('', $paseto->footer);

        $this->expectException(PasetoCryptoException::class);
        $paseto->decode(PasetoKey::make('3f09f3b08a4c50631b725da2397b4f4e3d976b01681703f4842a22501d6d0f7'));
    }
}
