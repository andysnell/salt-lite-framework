<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Routing\RequestHandler;

use Laminas\Diactoros\Uri;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\PageNotFoundResponse;
use PhoneBurner\SaltLite\Framework\Routing\RequestHandler\NullHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class NullHandlerTest extends TestCase
{
    #[Test]
    public function handle_returns_page_not_found(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('http://example.com/test/path?with=query'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->willReturnCallback(static function ($message, array $context): void {
                self::assertSame('middleware tried to handle a request with ' . NullHandler::class, $message);
                self::assertSame('http://example.com/test/path?with=query', $context['path']);
                self::assertArrayHasKey('stack', $context);
            });

        $sut = new NullHandler($logger);

        $response = $sut->handle($request);

        self::assertInstanceOf(PageNotFoundResponse::class, $response);
    }
}
