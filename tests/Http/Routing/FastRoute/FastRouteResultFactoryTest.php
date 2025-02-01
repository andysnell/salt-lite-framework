<?php

declare(strict_types=1);

namespace Http\Routing\FastRoute;

use FastRoute\Dispatcher;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteMatch;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteResultFactory as SUT;
use PhoneBurner\SaltLite\Framework\Http\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\MethodNotAllowed;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\RouteNotFound;
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
    public function make_returns_MethodNotAllowed(): void
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
    public function make_returns_RouteNotFound(): void
    {
        $result = $this->sut->make(FastRouteMatch::make([
            Dispatcher::NOT_FOUND,
        ]));

        self::assertInstanceOf(RouteNotFound::class, $result);
    }

    #[Test]
    public function make_returns_RouteFound(): void
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
