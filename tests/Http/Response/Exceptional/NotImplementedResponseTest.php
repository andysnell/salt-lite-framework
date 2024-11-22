<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\NotImplementedResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NotImplementedResponseTest extends TestCase
{
    #[Test]
    public function response_has_expected_defaults(): void
    {
        $sut = new NotImplementedResponse();

        self::assertSame(HttpStatus::NOT_IMPLEMENTED, $sut->getStatusCode());
        self::assertSame(HttpReasonPhrase::NOT_IMPLEMENTED, $sut->getStatusTitle());
        self::assertSame('This functionality is not yet implemented.', $sut->getStatusDetail());
        self::assertSame([], $sut->getAdditional());

        self::assertSame(HttpStatus::NOT_IMPLEMENTED, $sut->getCode());
        self::assertSame('HTTP 501: Not Implemented', $sut->getMessage());

        self::assertSame('text/plain; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 501: Not Implemented', $sut->getBody()->getContents());
    }
}
