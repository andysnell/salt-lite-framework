<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\String;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidStringLength;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\MessageSignature;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessageSignatureTest extends TestCase
{
    #[Test]
    public function happy_path_test(): void
    {
        $bytes = \random_bytes(MessageSignature::LENGTH);

        $signature = new MessageSignature($bytes);

        self::assertSame($bytes, $signature->bytes());
        self::assertSame(MessageSignature::LENGTH, $signature->length());

        $encoded = $signature->export();
        self::assertEquals($signature, MessageSignature::import($encoded));
        self::assertMatchesRegularExpression(ConstantTimeEncoding::BASE64URL_REGEX, (string)$signature);
    }

    #[Test]
    public function invalid_length_test_short(): void
    {
        $this->expectException(InvalidStringLength::class);
        new MessageSignature(\random_bytes(MessageSignature::LENGTH - 1));
    }

    #[Test]
    public function invalid_length_test_long(): void
    {
        $this->expectException(InvalidStringLength::class);
        new MessageSignature(\random_bytes(MessageSignature::LENGTH + 1));
    }

    #[Test]
    public function invalid_length_test_empty(): void
    {
        $this->expectException(InvalidStringLength::class);
        new MessageSignature('');
    }
}
