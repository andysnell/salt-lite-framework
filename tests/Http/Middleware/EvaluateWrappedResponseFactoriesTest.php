<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Middleware;

use PhoneBurner\ApiHandler\TransformableResponse;
use PhoneBurner\SaltLite\Framework\Http\Middleware\EvaluateWrappedResponseFactories;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ResponseException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EvaluateWrappedResponseFactoriesTest extends TestCase
{
    #[Test]
    #[TestWith([ResponseException::class])]
    #[TestWith([TransformableResponse::class])]
    public function process_transforms_wrapped_responses(string $class): void
    {
        self::assertTrue(\is_a($class, ResponseInterface::class, true));
        $response = $this->createMock($class);

        /** @phpstan-ignore phpunit.mockMethod (Can change if wrapped responses share a common interface) */
        $response->expects($this->once())
            ->method('getWrapped')
            ->willReturn($response);

        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $sut = new EvaluateWrappedResponseFactories();

        $this->assertSame($response, $sut->process($request, $handler));
    }


    #[Test]
    public function process_ignores_other_responses(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->never())->method(self::anything());

        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $sut = new EvaluateWrappedResponseFactories();

        $this->assertSame($response, $sut->process($request, $handler));
    }
}
