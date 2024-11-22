<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\GenericHttpExceptionResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GenericHttpExceptionResponseTest extends TestCase
{
    #[Test]
    public function response_has_expected_defaults(): void
    {
        $sut = new GenericHttpExceptionResponse();

        self::assertSame(HttpStatus::INTERNAL_SERVER_ERROR, $sut->getStatusCode());
        self::assertSame(HttpReasonPhrase::INTERNAL_SERVER_ERROR, $sut->getStatusTitle());
        self::assertSame('', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::INTERNAL_SERVER_ERROR, $sut->getCode());
        self::assertSame('HTTP 500: Internal Server Error', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 500: Internal Server Error', $sut->getBody()->getContents());
    }

    #[Test]
    public function response_can_be_customized(): void
    {
        $title = "I'm a teapot!";
        $detail = "I am a teapot, and thus refuse to brew coffee.";
        $additional = [
            'rfc' => 'https://www.rfc-editor.org/rfc/rfc2324#section-2.3.2',
        ];

        $sut = new GenericHttpExceptionResponse(HttpStatus::I_AM_A_TEAPOT, $title, $detail, $additional);

        self::assertSame(HttpStatus::I_AM_A_TEAPOT, $sut->getStatusCode());
        self::assertSame($title, $sut->getStatusTitle());
        self::assertSame($detail, $sut->getStatusDetail());
        self::assertSame($additional, $sut->getAdditional());

        self::assertSame(HttpStatus::I_AM_A_TEAPOT, $sut->getCode());
        self::assertSame("HTTP 418: I'm a teapot!", $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame("HTTP 418: I'm a teapot!", $sut->getBody()->getContents());
    }
}
