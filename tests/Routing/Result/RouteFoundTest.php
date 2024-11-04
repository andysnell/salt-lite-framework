<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Routing\Result;

use PhoneBurner\SaltLite\Framework\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Routing\RequestHandler\NullHandler;
use PhoneBurner\SaltLite\Framework\Routing\Result\RouteFound as SUT;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteFoundTest extends TestCase
{
    protected const array PATH_PARAMS = [
        'test' => 'data',
    ];

    protected const array ROUTE_ATTRIBUTES = [
        'route' => 'data',
    ];

    protected const array DEFAULT_ROUTE_ATTRIBUTES = [
        RequestHandlerInterface::class => NullHandler::class,
        MiddlewareInterface::class => [],
    ];

    protected RouteDefinition $definition;

    #[\Override]
    protected function setUp(): void
    {
        $this->definition = RouteDefinition::get('/path', self::ROUTE_ATTRIBUTES);
    }

    #[Test]
    public function make_returns_found(): void
    {
        $sut = SUT::make($this->definition, self::PATH_PARAMS);
        self::assertTrue($sut->isFound());
    }

    #[Test]
    public function make_returns_RouteMatch(): void
    {
        $sut = SUT::make($this->definition, self::PATH_PARAMS);

        $match = $sut->getRouteMatch();

        self::assertEquals([...self::DEFAULT_ROUTE_ATTRIBUTES, ...self::ROUTE_ATTRIBUTES], $match->getAttributes());

        self::assertEquals(self::PATH_PARAMS, $match->getPathParameters());
    }
}
