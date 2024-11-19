<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Routing\Middleware;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\MethodNotAllowedResponse;
use PhoneBurner\SaltLite\Framework\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Framework\Routing\Middleware\AttachRouteToRequest;
use PhoneBurner\SaltLite\Framework\Routing\Result\MethodNotAllowed;
use PhoneBurner\SaltLite\Framework\Routing\Result\RouteFound;
use PhoneBurner\SaltLite\Framework\Routing\Result\RouteNotFound;
use PhoneBurner\SaltLite\Framework\Routing\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AttachRouteToRequestTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<Router>
     */
    private ObjectProphecy $router;

    /**
     * @var ObjectProphecy<ServerRequestInterface>
     */
    private ObjectProphecy $request;

    /**
     * @var ObjectProphecy<RequestHandlerInterface>
     */
    private ObjectProphecy $next_handler;

    private ResponseInterface $response;

    private AttachRouteToRequest $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->next_handler = $this->prophesize(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->router = $this->prophesize(Router::class);

        $this->sut = new AttachRouteToRequest($this->router->reveal());
    }

    #[Test]
    public function process_throws_exception_if_match_method_is_not_allowed(): void
    {
        $methods = [HttpMethod::Get, HttpMethod::Post];

        $this->router->resolveForRequest($this->request->reveal())->willReturn(
            MethodNotAllowed::make(...$methods),
        );

        $response = $this->sut->process($this->request->reveal(), $this->next_handler->reveal());
        self::assertInstanceOf(MethodNotAllowedResponse::class, $response);
        self::assertSame($response->allowed_methods, [HttpMethod::Get, HttpMethod::Post]);
    }

    #[Test]
    public function process_attaches_route_when_match_is_found(): void
    {
        $route = RouteDefinition::get('/test');
        $result = RouteFound::make($route, ['path' => 'data']);

        $this->router->resolveForRequest($this->request->reveal())->willReturn(
            $result,
        );

        $request_with_route = $this->prophesize(ServerRequestInterface::class)->reveal();
        $this->request->withAttribute(
            RouteMatch::class,
            RouteMatch::make($route, ['path' => 'data']),
        )->willReturn($request_with_route)
            ->shouldBeCalledOnce();

        $this->next_handler->handle($request_with_route)
            ->willReturn($this->response)
            ->shouldBeCalled();

        $response = $this->sut->process($this->request->reveal(), $this->next_handler->reveal());

        self::assertSame($this->response, $response);
    }

    #[Test]
    public function process_passes_when_match_is_not_found(): void
    {
        $this->router->resolveForRequest($this->request->reveal())->willReturn(
            RouteNotFound::make(),
        );

        $this->next_handler->handle($this->request->reveal())
            ->willReturn($this->response)
            ->shouldBeCalled();

        $response = $this->sut->process($this->request->reveal(), $this->next_handler->reveal());

        self::assertSame($this->response, $response);
    }
}
