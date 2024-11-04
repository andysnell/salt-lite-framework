<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Routing\Definition;

use ArrayIterator;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Routing\Definition\RouteGroupDefinition as SUT;
use PhoneBurner\SaltLite\Framework\Tests\Fixtures\TestRequestHandler;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\MiddlewareInterface;
use stdClass;
use TypeError;

class RouteGroupDefinitionTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function make_prepends_path_to_all_routes(): void
    {
        $sut = SUT::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/root/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/root/path2'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function make_adds_methods_to_all_routes(): void
    {
        $methods = [HttpMethod::Patch, HttpMethod::Trace];

        $sut = SUT::make('', $methods);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post)
                ->withAddedMethod(...$methods),
            RouteDefinition::all('/path2'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function make_adds_attributes_to_all_routes(): void
    {
        $attributes = [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ];

        $sut = SUT::make('', [], $attributes);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value']),
            RouteDefinition::all('/path2', ['not_changed' => 'value']),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value'])->withAddedAttributes($attributes),
            RouteDefinition::all('/path2', ['not_changed' => 'value'])->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function serialization_maintains_state(): void
    {
        $route1 = RouteDefinition::get('/path1', ['replaced_attribute' => 'old_value']);
        $route2 = RouteDefinition::all('/path2', ['not_changed' => 'value']);

        $attributes = [
            'replaced_attribute' => 'new_value',
            'new_attribute' => 'value',
        ];

        $sut = SUT::make('/root', [HttpMethod::Trace], $attributes)->withRoutes(
            $route1,
            $route2,
        );

        $sut = \unserialize(\serialize($sut));

        self::assertEqualsCanonicalizing([
            $route1->withRoutePath('/root/path1')
                ->withAddedMethod(HttpMethod::Trace)
                ->withAddedAttributes($attributes),
            $route2->withRoutePath('/root/path2')
                ->withAddedMethod(HttpMethod::Trace)
                ->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function with_passes_self_to_methods_and_returns(): void
    {
        $sut = SUT::make('/test');

        $first = SUT::make('/first');
        $second = SUT::make('/second');
        $final = SUT::make('/final');

        self::assertSame(
            $final,
            $sut->with(static function (SUT $actual) use ($sut, $first): SUT {
                self::assertSame($actual, $sut);
                return $first;
            }, static function (SUT $actual) use ($first, $second): SUT {
                self::assertSame($first, $actual);
                return $second;
            }, static function (SUT $actual) use ($second, $final): SUT {
                self::assertSame($second, $actual);
                return $final;
            }),
        );
    }

    #[Test]
    public function with_rejects_any_none_self_return(): void
    {
        $sut = SUT::make('/test');

        $this->expectException(TypeError::class);
        $sut->with(static fn(): SUT => $sut, static fn(): stdClass => new stdClass());
    }

    #[Test]
    public function withRoutes_replaces_routes(): void
    {
        $sut = SUT::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        )->withRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAddedRoutes_adds_route(): void
    {
        $sut = SUT::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/root/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/root/path2'),
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withGroups_replaces_groups(): void
    {
        $sut = SUT::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        $group = $this->prophesize(SUT::class);
        $group->getIterator()->willReturn(Arr::iterable([
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        ]));

        $with_groups = $sut->withGroups($group->reveal());

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/root/path1'),
            RouteDefinition::all('/root/path2'),
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($with_groups));

        $without_groups = $with_groups->withGroups();

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($without_groups));
    }

    #[Test]
    public function withAddedGroups_adds_groups(): void
    {
        $sut = SUT::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        $group = $this->prophesize(SUT::class);
        $group->getIterator()->willReturn(new ArrayIterator([
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        ]));

        $with_groups = $sut->withGroups($group->reveal());

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
            RouteDefinition::get('/root/path1'),
            RouteDefinition::all('/root/path2'),
        ], \iterator_to_array($with_groups));

        $group = $this->prophesize(SUT::class);
        $group->getIterator()->willReturn(new ArrayIterator([
            RouteDefinition::get('/path5'),
            RouteDefinition::all('/path6'),
        ]));

        $with_more_groups = $with_groups->withAddedGroups($group->reveal());

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
            RouteDefinition::get('/root/path1'),
            RouteDefinition::all('/root/path2'),
            RouteDefinition::get('/root/path5'),
            RouteDefinition::all('/root/path6'),
        ], \iterator_to_array($with_more_groups));
    }

    #[Test]
    public function withRoutePath_changes_path(): void
    {
        $sut = SUT::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withRoutePath('/not_root');

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/not_root/path1'),
            RouteDefinition::all('/not_root/path2'),
            RouteDefinition::head('/not_root/path3'),
            RouteDefinition::delete('/not_root/path4'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withMethod_replaces_method(): void
    {
        $sut = SUT::make('', [HttpMethod::Get]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withMethod(HttpMethod::Trace);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Trace),
            RouteDefinition::all('/path2')->withAddedMethod(HttpMethod::Trace),
            RouteDefinition::head('/path3')->withAddedMethod(HttpMethod::Trace),
            RouteDefinition::delete('/path4')->withAddedMethod(HttpMethod::Trace),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAddedMethod_adds_method(): void
    {
        $sut = SUT::make('', [HttpMethod::Get]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withAddedMethod(HttpMethod::Trace);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
            RouteDefinition::all('/path2')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
            RouteDefinition::head('/path3')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
            RouteDefinition::delete('/path4')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAttributes_replaces_merged_array(): void
    {
        $attributes = [
            'test' => 'new',
            'test2' => 'new',
        ];

        $sut = SUT::make('', [], [
            'should' => 'not',
            'be' => 'used',
        ]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test' => 'old']),
            RouteDefinition::all('/path2'),
        )->withAttributes($attributes);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedAttributes($attributes),
            RouteDefinition::all('/path2')->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAddedAttributes_adds_merged_attributes(): void
    {
        $attributes = [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
            'existing' => 'test_value',
        ];

        $sut = SUT::make('', [], [
            'test_attribute2' => 'should_be_changed',
            'existing' => 'test_value',
        ]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value']),
            RouteDefinition::all('/path2', ['not_changed' => 'value']),
        )->withAddedAttributes([
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ]);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value'])->withAddedAttributes($attributes),
            RouteDefinition::all('/path2', ['not_changed' => 'value'])->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAttribute_sets_Attribute(): void
    {
        $attributes = [
            'test_attribute' => 'new_value',
            'test_attribute2' => 'test_value2',
        ];

        $sut = SUT::make('', [], [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value']),
            RouteDefinition::all('/path2', ['not_changed' => 'value']),
        )->withAttribute('test_attribute', 'new_value');

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value'])->withAddedAttributes($attributes),
            RouteDefinition::all('/path2', ['not_changed' => 'value'])->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withHandler_sets_handler_key(): void
    {
        $sut = SUT::make('');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withHandler(TestRequestHandler::class);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withHandler(TestRequestHandler::class),
            RouteDefinition::all('/path2')->withHandler(TestRequestHandler::class),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withMiddleware_adds_to_middleware(): void
    {
        /**
         * @var class-string<MiddlewareInterface>&string $old_middleware_class
         * @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface
         */
        $old_middleware_class = 'existing';

        /**
         * @var class-string<MiddlewareInterface> $new_middleware_class
         * @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface
         */
        $new_middleware_class = 'new_middleware';

        $sut = SUT::make('');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withMiddleware($old_middleware_class),
            RouteDefinition::all('/path2'),
        )->withMiddleware($new_middleware_class);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withMiddleware($new_middleware_class, $old_middleware_class),
            RouteDefinition::all('/path2')->withMiddleware($new_middleware_class),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withName_prepends_name(): void
    {
        $sut = SUT::make('');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withName('test'),
            RouteDefinition::all('/path2')->withName('test2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withName('group');

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withName('group.test'),
            RouteDefinition::all('/path2')->withName('group.test2'),
            RouteDefinition::head('/path3')->withName('group'),
            RouteDefinition::delete('/path4')->withName('group'),
        ], \iterator_to_array($sut));
    }
}
