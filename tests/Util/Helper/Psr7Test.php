<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\RequestFactory;
use PhoneBurner\SaltLite\Framework\Tests\Fixtures\TestRequestHandler;
use PhoneBurner\SaltLite\Framework\Util\Helper\Psr7;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Psr7Test extends TestCase
{
    #[Test]
    public function attribute_returns_null_when_attribute_not_found(): void
    {
        $request = new RequestFactory()->server(HttpMethod::Get, 'http://example.com');

        self::assertNull(Psr7::attribute(RequestHandlerInterface::class, $request));
    }

    #[Test]
    public function attribute_returns_null_when_attribute_not_instance(): void
    {
        $request = new RequestFactory()->server(HttpMethod::Get, 'http://example.com', attributes: [
            RequestHandlerInterface::class => new \stdClass(),
        ]);

        self::assertNull(Psr7::attribute(RequestHandlerInterface::class, $request));
    }

    #[Test]
    public function attribute_returns_instance_on_happy_path(): void
    {
        $handler = new TestRequestHandler();
        $request = new RequestFactory()->server(HttpMethod::Get, 'http://example.com', attributes: [
            RequestHandlerInterface::class => $handler,
        ]);

        self::assertSame($handler, Psr7::attribute(RequestHandlerInterface::class, $request));
    }

    #[Test]
    public function jsonBodyToArray_happy_path_with_message(): void
    {
        $array = [
            'foo' => 'bar',
            'baz' => 42,
        ];

        $message = $this->createMock(MessageInterface::class);
        $message->method('getBody')->willReturn(Str::stream(\json_encode($array, \JSON_THROW_ON_ERROR)));

        self::assertSame($array, Psr7::jsonBodyToArray($message));
    }

    #[Test]
    public function jsonBodyToArray_happy_path_with_stream(): void
    {
        $array = [
            'foo' => 'bar',
            'baz' => 42,
        ];

        $stream = Str::stream(\json_encode($array, \JSON_THROW_ON_ERROR));

        self::assertSame($array, Psr7::jsonBodyToArray($stream));
    }

    #[Test]
    public function jsonBodyToArray_happy_sad_path_invalid(): void
    {
        $array = [
            'foo' => 'bar',
            'baz' => 42,
        ];

        $stream = Str::stream(\substr(\json_encode($array, \JSON_THROW_ON_ERROR), 0, -1));

        self::assertNull(Psr7::jsonBodyToArray($stream));
    }

    #[Test]
    public function jsonBodyToArray_happy_sad_path_not_array(): void
    {
        $stream = Str::stream(\substr(\json_encode('false', \JSON_THROW_ON_ERROR), 0, -1));

        self::assertNull(Psr7::jsonBodyToArray($stream));
    }
}
