<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Routing\FastRoute;

use FastRoute\Dispatcher;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteMatch;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteResultFactory as SUT;
use PhoneBurner\SaltLite\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Http\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Http\Routing\Result\MethodNotAllowed;
use PhoneBurner\SaltLite\Http\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Http\Routing\Result\RouteNotFound;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FastRouteResultFactoryTest extends TestCase
{
    private SUT $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new SUT();
    }

    #[Test]
    public function makeReturnsMethodNotAllowed(): void
    {
        $result = $this->sut->make(FastRouteMatch::make([
            Dispatcher::METHOD_NOT_ALLOWED,
            [HttpMethod::Get, HttpMethod::Post],
        ]));

        self::assertInstanceOf(MethodNotAllowed::class, $result);
        self::assertEquals([
            HttpMethod::Get,
            HttpMethod::Post,
        ], $result->getAllowedMethods());
    }

    #[Test]
    public function makeReturnsRouteNotFound(): void
    {
        $result = $this->sut->make(FastRouteMatch::make([
            Dispatcher::NOT_FOUND,
        ]));

        self::assertInstanceOf(RouteNotFound::class, $result);
    }

    #[Test]
    public function makeReturnsRouteFound(): void
    {
        $route = RouteDefinition::all('/test', ['test' => 'value']);

        $result = $this->sut->make(FastRouteMatch::make([
            Dispatcher::FOUND,
            \serialize($route),
            ['path' => 'value'],
        ]));

        self::assertInstanceOf(RouteFound::class, $result);
        self::assertEquals(
            RouteMatch::make($route, ['path' => 'value']),
            $result->getRouteMatch(),
        );
    }
}
