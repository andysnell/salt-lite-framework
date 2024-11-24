<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\BadRequestResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BadRequestResponseTest extends TestCase
{
    #[Test]
    public function response_has_expected_defaults(): void
    {
        $sut = new BadRequestResponse();

        self::assertSame(HttpStatus::BAD_REQUEST, $sut->getStatusCode());
        self::assertSame(HttpReasonPhrase::BAD_REQUEST, $sut->getStatusTitle());
        self::assertSame('The request could not be understood by the server due to malformed syntax or invalid content.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::BAD_REQUEST, $sut->getCode());
        self::assertSame('HTTP 400: Bad Request', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 400: Bad Request', $sut->getBody()->getContents());
    }
}
