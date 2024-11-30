<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use PhoneBurner\SaltLite\Framework\Domain\Ip\IpAddress;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface, ServerRequestFactoryInterface
{
    public function fromGlobals(): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals()
            ->withAttribute(IpAddress::class, IpAddress::marshall($_SERVER));
    }

    public function createRequest(
        HttpMethod|string $method,
        mixed $uri,
        array $headers = [],
        StreamInterface $body = new Stream('php://temp', 'w+b'),
    ): Request {
        return new Request(
            $uri instanceof UriInterface ? $uri : new Uri($uri),
            HttpMethod::instance($method)->value,
            $body,
            $headers,
        );
    }

    public function createServerRequest(
        HttpMethod|string $method,
        mixed $uri,
        array $serverParams = [],
    ): ServerRequestInterface {
        $uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        return $this->server($method, $uri, server: $serverParams);
    }

    public function server(
        HttpMethod|string $method,
        UriInterface|string $uri,
        StreamInterface $body = new Stream('php://temp', 'w+b'),
        array $headers = [],
        array $server = [],
        array $query = [],
        array $cookies = [],
        array $files = [],
        array|object|null $parsed = null,
        string $protocol = '1.1',
        array $attributes = [],
    ): ServerRequestInterface {
        $request = new ServerRequest(
            $server,
            $files,
            $uri instanceof UriInterface ? $uri : new Uri($uri),
            HttpMethod::instance($method)->value,
            $body,
            $headers,
            $cookies,
            $query,
            $parsed,
            $protocol,
        );

        foreach ($attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $request;
    }
}
