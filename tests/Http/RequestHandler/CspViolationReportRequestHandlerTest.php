<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\RequestHandler;

use Laminas\Diactoros\ServerRequest;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\CspViolationReportRequestHandler;
use PhoneBurner\SaltLite\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Http\Response\EmptyResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CspViolationReportRequestHandlerTest extends TestCase
{
    #[Test]
    public function respondLogsReportedViolations(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('notice')->with('CSP Violation Reported', [
            "csp-report" => [
                "document-uri" => "https://example.com/foo/bar",
                "referrer" => "https://www.google.com/",
                "violated-directive" => "default-src self",
                "original-policy" => "default-src self; report-uri /csp-hotline.php",
                "blocked-uri" => "http://evilhackerscripts.com",
            ],
        ]);

        $request = new ServerRequest(
            method: HttpMethod::Post->value,
            headers: [HttpHeader::CONTENT_TYPE => ContentType::JSON],
            parsedBody: [
                "csp-report" => [
                    "document-uri" => "https://example.com/foo/bar",
                    "referrer" => "https://www.google.com/",
                    "violated-directive" => "default-src self",
                    "original-policy" => "default-src self; report-uri /csp-hotline.php",
                    "blocked-uri" => "http://evilhackerscripts.com",
                ],
            ],
        );

        $sut = new CspViolationReportRequestHandler($logger);
        $response = $sut->handle($request);

        self::assertInstanceOf(EmptyResponse::class, $response);
        self::assertSame(HttpStatus::ACCEPTED, $response->getStatusCode());
    }

    #[Test]
    public function respondHandlesTheEmptyCase(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('notice')->with('CSP Violation Reported', []);

        $request = new ServerRequest()
            ->withMethod(HttpMethod::Post->value)
            ->withHeader(HttpHeader::CONTENT_TYPE, ContentType::JSON)
            ->withParsedBody(null);

        $sut = new CspViolationReportRequestHandler($logger);
        $response = $sut->handle($request);

        self::assertInstanceOf(EmptyResponse::class, $response);
        self::assertSame(HttpStatus::ACCEPTED, $response->getStatusCode());
    }
}
