<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Routing\FastRoute;

use ArrayIterator;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Generator;
use IteratorAggregate;
use LogicException;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\DefinitionList;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\InMemoryDefinitionList;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteDispatcherFactory;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteMatch;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouter as SUT;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteResultFactory;
use PhoneBurner\SaltLite\Framework\Http\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\MethodNotAllowed;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\RouteNotFound;
use PhoneBurner\SaltLite\Framework\Http\Routing\RouterResult;
use PhoneBurner\SaltLite\Framework\Tests\TestSupport\MockRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

final class FastRouterTest extends TestCase
{
    use MockRequest;
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<DefinitionList>
     */
    private ObjectProphecy $definition_list;

    /**
     * @var ObjectProphecy<FastRouteDispatcherFactory>
     */
    private ObjectProphecy $dispatcher_factory;

    /**
     * @var ObjectProphecy<Dispatcher>
     */
    private ObjectProphecy $dispatcher;

    /**
     * @var ObjectProphecy<FastRouteResultFactory>
     */
    private ObjectProphecy $result_factory;

    private SUT $sut;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->definition_list = $this->prophesize(DefinitionList::class);
        $this->definition_list->willImplement(IteratorAggregate::class);
        $this->dispatcher_factory = $this->prophesize(FastRouteDispatcherFactory::class);
        $this->result_factory = $this->prophesize(FastRouteResultFactory::class);

        $this->sut = new SUT(
            $this->definition_list->reveal(),
            $this->dispatcher_factory->reveal(),
            $this->result_factory->reveal(),
        );

        $this->dispatcher = $this->prophesize(Dispatcher::class);
    }

    #[Test]
    public function resolveByName_returns_RouteFound(): void
    {
        $route = RouteDefinition::all('/test');
        $this->definition_list->getNamedRoute('test')->willReturn($route);

        $result = $this->sut->resolveByName('test');

        self::assertInstanceOf(RouteFound::class, $result);
        self::assertEquals(
            RouteFound::make($route),
            $result,
        );
    }

    #[Test]
    public function resolveByName_returns_RouteNotFound(): void
    {
        $this->definition_list->getNamedRoute('test')->willThrow(LogicException::class);

        $result = $this->sut->resolveByName('test');

        self::assertInstanceOf(RouteNotFound::class, $result);
    }

    /**
     * This is more of an integration test, but useful in verifying we're using
     * fast route as expected.
     */
    #[Test]
    public function resolveForRequest_returns_expected_results(): void
    {
        $all_route = RouteDefinition::all('/all[/{id:\d+}]', ['route' => 'all']);
        $get_route = RouteDefinition::get('/get', ['route' => 'get']);

        $definition_list = InMemoryDefinitionList::make(
            $all_route,
            $get_route,
        );

        $sut = new SUT(
            $definition_list,
            new FastRouteDispatcherFactory(
                $this->createMock(LoggerInterface::class),
                false,
                '/tmp/should_not_be_used',
            ),
            new FastRouteResultFactory(),
        );

        $get = $this->buildMockRequest()
            ->withRequestMethod(HttpMethod::Get)
            ->withUri('http://example.com/get')
            ->make();

        $result = $sut->resolveForRequest($get);
        self::assertInstanceOf(RouteFound::class, $result);
        self::assertEquals(RouteMatch::make($get_route), $result->getRouteMatch());

        $post = $this->buildMockRequest()
            ->withRequestMethod(HttpMethod::Post)
            ->withUri('http://example.com/all/100')
            ->make();

        $result = $sut->resolveForRequest($post);
        self::assertInstanceOf(RouteFound::class, $result);
        self::assertEquals(RouteMatch::make($all_route, ['id' => 100]), $result->getRouteMatch());

        $bad = $this->buildMockRequest()
            ->withRequestMethod(HttpMethod::Post)
            ->withUri('http://example.com/bad')
            ->make();

        $result = $sut->resolveForRequest($bad);
        self::assertInstanceOf(RouteNotFound::class, $result);

        $bad = $this->buildMockRequest()
            ->withRequestMethod(HttpMethod::Post)
            ->withUri('http://example.com/get')
            ->make();

        $result = $sut->resolveForRequest($bad);
        self::assertInstanceOf(MethodNotAllowed::class, $result);
        self::assertEquals([HttpMethod::Get], $result->getAllowedMethods());
    }

    #[DataProvider('provideFastRouteMatchData')]
    #[Test]
    public function resolveForRequest_provides_callback_that_loads_routes_and_provides_to_fast_route_collector(
        array $match,
    ): void {
        $route1 = RouteDefinition::all('/all', ['route' => 'data']);
        $route2 = RouteDefinition::get('/get', ['route' => 'data']);

        $this->definition_list->getIterator()->willReturn(new ArrayIterator([
            $route1,
            $route2,
        ]));

        $callable = null;
        $this->dispatcher_factory->make(Argument::that(static function (callable $arg) use (&$callable): bool {
            $callable = $arg;
            return true;
        }), Argument::any())
            ->willReturn($this->dispatcher->reveal());

        $this->dispatcher->dispatch(Argument::cetera())->willReturn($match);

        $result = $this->prophesize(RouterResult::class)->reveal();
        $this->result_factory->make(FastRouteMatch::make($match))->willReturn($result);

        $this->sut->resolveForRequest($this->getMockRequest());

        // now check that the route collector is given the right data
        $collector = $this->prophesize(RouteCollector::class);
        self::assertIsCallable($callable);
        $callable($collector->reveal());

        $collector->addRoute(
            $route1->getMethods(),
            '/all',
            \serialize($route1),
        )->shouldHaveBeenCalled();

        $collector->addRoute(
            [HttpMethod::Get->value],
            '/get',
            \serialize($route2),
        )->shouldHaveBeenCalled();
    }

    #[DataProvider('provideTestMatchData')]
    #[Test]
    public function resolveForRequest_returns_factory_response(
        HttpMethod|string $method,
        string $uri,
        array $match,
    ): void {
        $dispatcher = $this->dispatcher->reveal();

        $this->dispatcher_factory->make(Argument::cetera())
            ->willReturn($dispatcher);

        $this->dispatcher->dispatch(HttpMethod::instance($method)->value, $uri)
            ->willReturn($match);

        $result = $this->prophesize(RouterResult::class)->reveal();
        $this->result_factory->make(FastRouteMatch::make($match))->willReturn($result);

        $request = $this->buildMockRequest()
            ->withRequestMethod($method)
            ->withUri($uri)
            ->make();

        self::assertSame($result, $this->sut->resolveForRequest($request));
    }

    public static function provideFastRouteMatchData(): Generator
    {
        yield 'not found' => [[
            Dispatcher::NOT_FOUND,
        ],];

        yield 'method not allowed' => [[
            Dispatcher::METHOD_NOT_ALLOWED,
            [HttpMethod::Get, HttpMethod::Delete],
        ],];

        yield 'found' => [[
            Dispatcher::FOUND,
            'serialized data',
            ['path' => 'data'],
        ],];
    }

    public static function provideTestMatchData(): Generator
    {
        foreach (self::provideFastRouteMatchData() as $label => [$data]) {
            yield 'get that is ' . $label => [
                HttpMethod::Get,
                'foo/bar',
                $data,
            ];

            yield 'post that is ' . $label => [
                HttpMethod::Post,
                'biz/baz',
                $data,
            ];
        }
    }
}
