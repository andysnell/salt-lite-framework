<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\String;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConstantTimeEncodingTest extends TestCase
{
    #[Test]
    public function prefix_returns_expected_value(): void
    {
        self::assertSame('hex:', ConstantTimeEncoding::Hex->prefix());
        self::assertSame('base64:', ConstantTimeEncoding::Base64->prefix());
        self::assertSame('base64:', ConstantTimeEncoding::Base64NoPadding->prefix());
        self::assertSame('base64url:', ConstantTimeEncoding::Base64Url->prefix());
        self::assertSame('base64url:', ConstantTimeEncoding::Base64UrlNoPadding->prefix());
    }

    #[Test]
    public function regex_returns_expected_value(): void
    {
        self::assertSame('/^[A-Fa-f0-9]+$/', ConstantTimeEncoding::Hex->regex());
        self::assertSame('/^[A-Za-z0-9+\/]+={0,2}$/', ConstantTimeEncoding::Base64->regex());
        self::assertSame('/^[A-Za-z0-9+\/]+$/', ConstantTimeEncoding::Base64NoPadding->regex());
        self::assertSame('/^[A-Za-z0-9-_]+={0,2}$/', ConstantTimeEncoding::Base64Url->regex());
        self::assertSame('/^[A-Za-z0-9-_]+$/', ConstantTimeEncoding::Base64UrlNoPadding->regex());
    }

    #[Test]
    #[DataProvider('providesEncodingHappyPathTests')]
    public function happy_path_encoding_and_decoding(
        ConstantTimeEncoding $encoding,
        bool $prefix,
        string $input,
        string $expected,
    ): void {
        self::assertSame($expected, $encoding->encode($input, $prefix));
        self::assertSame($input, $encoding->decode($expected));
    }

    public static function providesEncodingHappyPathTests(): \Generator
    {
        yield [ConstantTimeEncoding::Hex, false, 'hello', '68656c6c6f'];
        yield [ConstantTimeEncoding::Hex, true, 'hello', 'hex:68656c6c6f'];

        yield [ConstantTimeEncoding::Base64, false, 'hello', 'aGVsbG8='];
        yield [ConstantTimeEncoding::Base64, true, 'hello', 'base64:aGVsbG8='];

        yield [ConstantTimeEncoding::Base64NoPadding, false, 'hello', 'aGVsbG8'];
        yield [ConstantTimeEncoding::Base64NoPadding, true, 'hello', 'base64:aGVsbG8'];

        yield [ConstantTimeEncoding::Base64Url, false, 'hello', 'aGVsbG8='];
        yield [ConstantTimeEncoding::Base64Url, true, 'hello', 'base64url:aGVsbG8='];

        yield [ConstantTimeEncoding::Base64UrlNoPadding, false, 'hello', 'aGVsbG8'];
        yield [ConstantTimeEncoding::Base64UrlNoPadding, true, 'hello', 'base64url:aGVsbG8'];

        yield [ConstantTimeEncoding::Hex, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', '54686520517569636b2042726f776e20466f78204a756d7073204f76657220546865204c617a7920446f67'];
        yield [ConstantTimeEncoding::Hex, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'hex:54686520517569636b2042726f776e20466f78204a756d7073204f76657220546865204c617a7920446f67'];

        yield [ConstantTimeEncoding::Base64, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];
        yield [ConstantTimeEncoding::Base64, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];

        yield [ConstantTimeEncoding::Base64NoPadding, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];
        yield [ConstantTimeEncoding::Base64NoPadding, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];

        yield [ConstantTimeEncoding::Base64Url, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];
        yield [ConstantTimeEncoding::Base64Url, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64url:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw=='];

        yield [ConstantTimeEncoding::Base64UrlNoPadding, false, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];
        yield [ConstantTimeEncoding::Base64UrlNoPadding, true, 'The Quick Brown Fox Jumps Over The Lazy Dog', 'base64url:VGhlIFF1aWNrIEJyb3duIEZveCBKdW1wcyBPdmVyIFRoZSBMYXp5IERvZw'];

        yield [ConstantTimeEncoding::Hex , false, '📞🔥', 'f09f939ef09f94a5'];
        yield [ConstantTimeEncoding::Base64, false, '📞🔥', '8J+TnvCflKU='];
        yield [ConstantTimeEncoding::Base64NoPadding, false, '📞🔥', '8J+TnvCflKU'];
        yield [ConstantTimeEncoding::Base64Url, false, '📞🔥', '8J-TnvCflKU='];
        yield [ConstantTimeEncoding::Base64UrlNoPadding, false, '📞🔥', '8J-TnvCflKU'];

        yield [ConstantTimeEncoding::Hex , false, "\xff\xff\xfe\xff", 'fffffeff'];
        yield [ConstantTimeEncoding::Base64, false, "\xff\xff\xfe\xff", '///+/w=='];
        yield [ConstantTimeEncoding::Base64NoPadding, false, "\xff\xff\xfe\xff", '///+/w'];
        yield [ConstantTimeEncoding::Base64Url, false, "\xff\xff\xfe\xff", '___-_w=='];
        yield [ConstantTimeEncoding::Base64UrlNoPadding, false, "\xff\xff\xfe\xff", '___-_w'];
    }

    #[Test]
    #[DataProvider('providesInvalidInputForDecoding')]
    public function decode_throws_exception_on_invalid_input(
        ConstantTimeEncoding $encoding,
        string $input,
    ): void {
        $this->expectException(\UnexpectedValueException::class);
        $encoding->decode($input);
    }

    public static function providesInvalidInputForDecoding(): \Generator
    {
        yield [ConstantTimeEncoding::Hex, 'invalid'];
        yield [ConstantTimeEncoding::Hex, '68656c6c6'];
        yield [ConstantTimeEncoding::Base64, 'this is an invalid base64 string!'];
        yield [ConstantTimeEncoding::Base64NoPadding, 'this is an invalid base64 string!'];
        yield [ConstantTimeEncoding::Base64Url, 'this is an invalid base64 string!'];
        yield [ConstantTimeEncoding::Base64UrlNoPadding, 'this is an invalid base64 string!'];
    }

    #[Test]
    public function hex_prefixes_are_stripped(): void
    {
        self::assertSame('hello', ConstantTimeEncoding::Hex->decode('hex:68656c6c6f'));
        self::assertSame('hello', ConstantTimeEncoding::Hex->decode('0x68656c6c6f'));
        self::assertSame('hello', ConstantTimeEncoding::Hex->decode('hex:0x68656c6c6f'));
    }
}
