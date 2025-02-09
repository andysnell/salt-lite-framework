<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Routing\RequestHandler;

use Laminas\Diactoros\Uri;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\NotFoundResponse;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class NotFoundRequestHandlerTest extends TestCase
{
    #[Test]
    public function handle_returns_page_not_found(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('http://example.com/test/path?with=query'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('notice')
            ->willReturnCallback(static function ($message, array $context): void {
                self::assertSame('Not Found: {path}', $message);
                self::assertSame('http://example.com/test/path?with=query', $context['path']);
            });

        $sut = new NotFoundRequestHandler($logger);

        $response = $sut->handle($request);

        self::assertInstanceOf(NotFoundResponse::class, $response);
    }
}
