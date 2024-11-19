<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Routing\Definition;

use Generator;
use PhoneBurner\SaltLite\Framework\Routing\Definition\LazyConfigDefinitionList as SUT;
use PhoneBurner\SaltLite\Framework\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Routing\Definition\RouteGroupDefinition;
use PhoneBurner\SaltLite\Framework\Routing\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LazyConfigDefinitionListTest extends TestCase
{
    private static bool $called = false;

    /**
     * @var RouteDefinition[]
     */
    private array $expected;

    #[\Override]
    protected function setUp(): void
    {
        self::$called = false;

        $this->expected = [
            RouteDefinition::get('/route')->withName('route'),
            RouteDefinition::all('/group/1')->withName('group.route1'),
            RouteDefinition::post('/group/2')->withName('group.route2'),
            RouteDefinition::get('/array1')->withName('array1'),
            RouteDefinition::get('/array2')->withName('array2'),
            RouteDefinition::all('/array3/group')->withName('array3.route'),
            RouteDefinition::all('/all')->withName('all'),
        ];
    }

    #[DataProvider('provideMakeMethodAndArgs')]
    #[Test]
    public function iterating_lazy_loads_expected_list(string $method, array $args): void
    {
        self::assertFalse(self::$called);

        $sut = SUT::$method(...$args);

        self::assertEqualsCanonicalizing(
            $this->expected,
            \iterator_to_array($sut, false),
        );

        self::assertTrue(self::$called);
    }

    #[DataProvider('provideMakeMethodAndArgs')]
    #[Test]
    public function getNamedRoute_lazy_loads_expected_list(string $method, array $args): void
    {
        self::assertFalse(self::$called);

        $sut = SUT::$method(...$args);

        foreach ($this->expected as $expected) {
            self::assertEquals(
                $expected,
                $sut->getNamedRoute($expected->getAttributes()[Route::class]),
            );
        }

        self::assertTrue(self::$called);
    }

    #[DataProvider('provideMakeMethodAndArgs')]
    #[Test]
    public function hasNamedRoute_returns_expected(string $method, array $args): void
    {
        $sut = SUT::$method(...$args);

        foreach ($this->expected as $expected) {
            self::assertTrue($sut->hasNamedRoute($expected->getAttributes()[Route::class]));
        }

        self::assertFalse($sut->hasNamedRoute('not_a_route_that_exists'));
    }

    public static function provideMakeMethodAndArgs(): Generator
    {
        $callables = [
            static fn(): RouteDefinition => RouteDefinition::get('/route')->withName('route'),
            static fn(): RouteGroupDefinition => RouteGroupDefinition::make('/group')
                ->withName('group')
                ->withRoutes(
                    RouteDefinition::all('/1')->withName('route1'),
                    RouteDefinition::post('/2')->withName('route2'),
                ),
            static fn(): array => [
                RouteDefinition::get('/array1')->withName('array1'),
                RouteDefinition::get('/array2')->withName('array2'),
                RouteGroupDefinition::make('/array3/group')
                    ->withName('array3')
                    ->withRoutes(
                        RouteDefinition::all('')->withName('route'),
                    ),
                ],
            static function (): RouteDefinition {
                self::$called = true;
                return RouteDefinition::all('/all')->withName('all');
            },
        ];

        $config = [
            'route' => $callables[0],
            'group' => $callables[1],
            'array' => $callables[2],
            'observer' => $callables[3],
        ];

        $array = [
            $callables[0],
            $callables[1],
            $callables[2],
            $callables[3],
        ];

        yield 'callables' => ['makeFromCallable', [...$callables]];
        yield 'config' => ['makeFromArray', [$config]];
        yield 'array' => ['makeFromArray', [$array]];
    }
}
