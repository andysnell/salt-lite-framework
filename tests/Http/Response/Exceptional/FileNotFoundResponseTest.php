<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\FileNotFoundResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileNotFoundResponseTest extends TestCase
{
    #[Test]
    public function response_has_expected_defaults(): void
    {
        $sut = new FileNotFoundResponse();

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getStatusCode());
        self::assertSame('File Not Found', $sut->getStatusTitle());
        self::assertSame('The file requested could not be found.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::NOT_FOUND, $sut->getCode());
        self::assertSame('HTTP 404: File Not Found', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 404: File Not Found', $sut->getBody()->getContents());
    }
}
