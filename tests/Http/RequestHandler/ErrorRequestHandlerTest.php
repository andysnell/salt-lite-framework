<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\RequestHandler;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\RequestHandler\ErrorRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\PageNotFoundResponse;
use PhoneBurner\SaltLite\Framework\Routing\Match\RouteMatch;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class ErrorRequestHandlerTest extends TestCase
{
    #[TestWith([400])]
    #[TestWith([403])]
    #[TestWith([404])]
    #[TestWith([418])]
    #[TestWith([451])]
    #[TestWith([500])]
    #[Test]
    public function handle_returns_mapped_error_response(int $status_code): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->expects($this->once())
            ->method('getPathParameter')
            ->with('error')
            ->willReturn((string)$status_code);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $sut = new ErrorRequestHandler();

        $response = $sut->handle($request);

        self::assertInstanceOf(HttpExceptionResponse::class, $response);
        self::assertSame($response->getStatusCode(), $status_code);
        self::assertSame($response->getStatusTitle(), HttpReasonPhrase::lookup($status_code));
    }

    #[TestWith([null])]
    #[TestWith(['page-not-found'])]
    #[TestWith([666])]
    #[TestWith([''])]
    #[Test]
    public function handle_returns_fallback_404_response(mixed $error_param): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->expects($this->once())
            ->method('getPathParameter')
            ->with('error')
            ->willReturn($error_param);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $sut = new ErrorRequestHandler();

        $response = $sut->handle($request);

        self::assertInstanceOf(PageNotFoundResponse::class, $response);
    }
}
