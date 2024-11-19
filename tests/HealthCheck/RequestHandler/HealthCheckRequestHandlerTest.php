<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\HealthCheck\RequestHandler;

use Laminas\Diactoros\Uri;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthCheck;
use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PhoneBurner\SaltLite\Framework\HealthCheck\HealthCheckBuilder;
use PhoneBurner\SaltLite\Framework\HealthCheck\RequestHandler\HealthCheckRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class HealthCheckRequestHandlerTest extends TestCase
{
    #[Test]
    #[TestWith([HealthStatus::Pass, HttpStatus::OK])]
    #[TestWith([HealthStatus::Warn, HttpStatus::SERVICE_UNAVAILABLE])]
    #[TestWith([HealthStatus::Fail, HttpStatus::SERVICE_UNAVAILABLE])]
    public function happy_path(HealthStatus $health_status, int $http_status): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn(new Uri('http://localhost/health'));

        $health_check = new HealthCheck(status: $health_status);
        $factory = $this->createMock(HealthCheckBuilder::class);
        $factory->expects($this->once())
            ->method('withLinks')
            ->with(['self' => '/health'])
            ->willReturnSelf();
        $factory->expects($this->once())
            ->method('make')
            ->willReturn($health_check);

        $handler = new HealthCheckRequestHandler($factory);
        $response = $handler->handle($request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame($http_status, $response->getStatusCode());
        self::assertEquals($health_check, $response->getPayload());
        self::assertSame(ContentType::HEALTH_JSON, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('no-store', $response->getHeaderLine(HttpHeader::CACHE_CONTROL));
    }
}
