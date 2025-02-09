<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Routing\RequestHandler;

use LogicException;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\RedirectResponse;
use PhoneBurner\SaltLite\Framework\Http\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\RedirectRequestHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class RedirectRequestHandlerTest extends TestCase
{
    private RouteMatch&MockObject $route_match;

    private ServerRequestInterface&MockObject $request;

    #[\Override]
    protected function setUp(): void
    {
        $this->route_match = $this->createMock(RouteMatch::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->request->method('getAttribute')->with(RouteMatch::class)->willReturn($this->route_match);
    }

    #[Test]
    public function handle_throws_exception_if_route_match_missing(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $sut = new RedirectRequestHandler();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Request is Missing Required RouteMatch Attribute');
        $sut->handle($request);
    }

    #[Test]
    public function handle_throws_exception_if_redirect_uri_invalid(): void
    {
        $this->route_match->method('getAttributes')->willReturn([]);

        $sut = new RedirectRequestHandler();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Request has Invalid Redirect URI');
        $sut->handle($this->request);
    }

    #[Test]
    public function handle_throws_exception_if_redirect_status_code_missing(): void
    {
        $this->route_match->method('getAttributes')->willReturn([
            RedirectRequestHandler::URI => '/foo/bar',
        ]);

        $sut = new RedirectRequestHandler();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Request has Invalid Redirect Status Code');
        $sut->handle($this->request);
    }

    #[Test]
    public function handle_throws_exception_if_redirect_status_code_invalid(): void
    {
        $this->route_match->method('getAttributes')->willReturn([
            RedirectRequestHandler::URI => '/foo/bar',
            RedirectRequestHandler::STATUS_CODE => HttpStatus::BAD_REQUEST,
        ]);

        $sut = new RedirectRequestHandler();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Request has Invalid Redirect Status Code');
        $sut->handle($this->request);
    }

    #[TestWith([301])]
    #[TestWith([302])]
    #[TestWith([303])]
    #[TestWith([307])]
    #[TestWith([308])]
    #[Test]
    public function handle_returns_expected_redirect_response(int $status_code): void
    {
        $path = '/foo/bar';
        $this->route_match->method('getAttributes')->willReturn([
            RedirectRequestHandler::URI => $path,
            RedirectRequestHandler::STATUS_CODE => $status_code,
        ]);

        $sut = new RedirectRequestHandler();
        $response = $sut->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame($path, $response->getHeaderLine(HttpHeader::LOCATION));
        self::assertSame($status_code, $response->getStatusCode());
    }
}
