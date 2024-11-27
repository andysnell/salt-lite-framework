<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\RequestFactory;
use PhoneBurner\SaltLite\Framework\Tests\Fixtures\TestRequestHandler;
use PhoneBurner\SaltLite\Framework\Util\Helper\Psr7;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class Psr7Test extends TestCase
{
    #[Test]
    public function attribute_returns_null_when_attribute_not_found(): void
    {
        $request = new RequestFactory()->server(HttpMethod::Get, 'http://example.com');

        self::assertNull(Psr7::attribute($request, RequestHandlerInterface::class));
    }

    #[Test]
    public function attribute_returns_null_when_attribute_not_instance(): void
    {
        $request = new RequestFactory()->server(HttpMethod::Get, 'http://example.com', attributes: [
            RequestHandlerInterface::class => new \stdClass(),
        ]);

        self::assertNull(Psr7::attribute($request, RequestHandlerInterface::class));
    }


    #[Test]
    public function attribute_returns_instance_on_happy_path(): void
    {
        $handler = new TestRequestHandler();
        $request = new RequestFactory()->server(HttpMethod::Get, 'http://example.com', attributes: [
            RequestHandlerInterface::class => $handler,
        ]);

        self::assertSame($handler, Psr7::attribute($request, RequestHandlerInterface::class));
    }
}
