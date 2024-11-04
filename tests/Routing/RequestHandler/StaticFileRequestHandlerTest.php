<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Routing\RequestHandler;

use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\FileNotFoundResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ServerErrorResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\StreamResponse;
use PhoneBurner\SaltLite\Framework\Routing\Domain\StaticFile;
use PhoneBurner\SaltLite\Framework\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Framework\Routing\RequestHandler\StaticFileRequestHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use const PhoneBurner\SaltLite\Framework\UNIT_TEST_ROOT;

class StaticFileRequestHandlerTest extends TestCase
{
    protected const string GOOD_FILE = UNIT_TEST_ROOT . '/Fixtures/2500x2500.png';

    private StaticFileRequestHandler $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new StaticFileRequestHandler();
    }

    #[Test]
    public function missing_route_match_returns_server_error_response(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn(null);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(ServerErrorResponse::class, $response);
    }

    #[DataProvider('providesInvalidStaticFile')]
    #[Test]
    public function invalid_static_file_returns_server_error_response(array $attributes): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn($attributes);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(ServerErrorResponse::class, $response);
    }

    public static function providesInvalidStaticFile(): array
    {
        return [
            [[]],
            [[StaticFile::class => new \stdClass()]],
        ];
    }

    #[Test]
    public function bad_file_returns_file_not_found_response(): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn([
            StaticFile::class => new StaticFile('bad_file', ContentType::HTML),
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(FileNotFoundResponse::class, $response);
    }

    #[Test]
    public function valid_static_file_returns_inline_stream_response(): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn([
            StaticFile::class => new StaticFile(self::GOOD_FILE, ContentType::PNG),
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(StreamResponse::class, $response);
        self::assertSame(ContentType::PNG, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame((string)\filesize(self::GOOD_FILE), $response->getHeaderLine(HttpHeader::CONTENT_LENGTH));
        self::assertSame('inline', $response->getHeaderLine(HttpHeader::CONTENT_DISPOSITION));
        self::assertStringEqualsFile(self::GOOD_FILE, (string)$response->getBody());
    }

    #[Test]
    public function valid_static_file_returns_attachment_stream_response(): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn([
            StaticFile::class => new StaticFile(self::GOOD_FILE, ContentType::PNG),
            HttpHeader::CONTENT_DISPOSITION => 'attachment',
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(StreamResponse::class, $response);
        self::assertSame(ContentType::PNG, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame((string)\filesize(self::GOOD_FILE), $response->getHeaderLine(HttpHeader::CONTENT_LENGTH));
        self::assertSame('attachment', $response->getHeaderLine(HttpHeader::CONTENT_DISPOSITION));
        self::assertStringEqualsFile(self::GOOD_FILE, (string)$response->getBody());
    }
}
