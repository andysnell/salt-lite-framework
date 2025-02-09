<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\NotFoundResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotFoundResponseTest extends TestCase
{
    #[Test]
    public function response_has_expected_defaults(): void
    {
        $sut = new NotFoundResponse();

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getStatusCode());
        self::assertSame('Not Found', $sut->getStatusTitle());
        self::assertSame('', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getCode());
        self::assertSame('HTTP 404: Not Found', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 404: Not Found', $sut->getBody()->getContents());
    }
}
