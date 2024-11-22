<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ResponseException;
use PhoneBurner\SaltLite\Framework\Http\Response\HtmlResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResponseExceptionTest extends TestCase
{
    #[Test]
    public function response_has_expected_defaults(): void
    {
        $response = new HtmlResponse('Hello, World');

        $sut = new ResponseException($response);

        self::assertSame(HttpStatus::OK, $sut->getStatusCode());
        self::assertSame('text/html; charset=utf-8', $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('Hello, World', $sut->getBody()->getContents());
        self::assertNull($sut->getPrevious());
        self::assertSame('', $sut->getMessage());
        self::assertSame(HttpStatus::OK, $sut->getCode());
    }

    #[Test]
    public function response_can_return_response_with_exception_message_and_previous(): void
    {
        $previous = new \RuntimeException('Test');
        $response = new JsonResponse(['message' => 'Hello, World'], 444);

        $sut = new ResponseException($response, 'Test Message', $previous);

        self::assertSame(444, $sut->getStatusCode());
        self::assertSame(ContentType::JSON, $sut->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('{"message":"Hello, World"}', $sut->getBody()->getContents());
        self::assertSame($previous, $sut->getPrevious());
        self::assertSame('Test Message', $sut->getMessage());
        self::assertSame(444, $sut->getCode());
    }
}
