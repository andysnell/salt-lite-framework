<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\PageNotFoundResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PageNotFoundResponseTest extends TestCase
{
    #[Test]
    public function response_has_expected_defaults(): void
    {
        $sut = new PageNotFoundResponse();

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getStatusCode());
        self::assertSame('Page Not Found', $sut->getStatusTitle());
        self::assertSame('The requested page could not be found.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getCode());
        self::assertSame('HTTP 404: Page Not Found', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 404: Page Not Found', $sut->getBody()->getContents());
    }
}
