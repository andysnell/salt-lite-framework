<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Domain;

enum HttpMethod: string
{
    /**
     * Request a representation of the specified resource.
     *
     * Pure: True
     * Idempotent: True
     * Cacheable: True
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.1
     */
    case Get = 'GET';

    /**
     * Ask for a response identical to that of the corresponding GET request,
     * but without the response body
     *
     * Pure: True
     * Idempotent: True
     * Cacheable: True
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.2
     */
    case Head = 'HEAD';

    /**
     * Used to submit an entity to the specified resource, often causing a
     * change in state or side effects on the server
     *
     * Pure: False
     * Idempotent: False
     * Cacheable: True
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.3
     */
    case Post = 'POST';

    /**
     * Replace the target resource with the request payload
     *
     * Pure: False
     * Idempotent: True
     * Cacheable: False
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.4
     */
    case Put = 'PUT';

    /**
     * Delete the specified resource
     *
     * Pure: False
     * Idempotent: True
     * Cacheable: False
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.5
     */
    case Delete = 'DELETE';

    /**
     * Establish a tunnel to the server identified by the target resource
     *
     * Pure: False
     * Idempotent: False
     * Cacheable: False
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/CONNECT
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.6
     */
    case Connect = 'CONNECT';

    /**
     * Describe the communication options for the target resource
     *
     * Pure: True
     * Idempotent: True
     * Cacheable: False
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.7
     */
    case Options = 'OPTIONS';

    /**
     * Perform a message loop-back test along the path to the target resource
     *
     * Pure: True
     * Idempotent: True
     * Cacheable: False
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/TRACE
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.8
     */
    case Trace = 'TRACE';

    /**
     * Apply partial modifications to a resource
     *
     * Pure: False
     * Idempotent: False
     * Cacheable: False
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PATCH
     * @link https://tools.ietf.org/html/rfc5789
     */
    case Patch = 'PATCH';

    public static function instance(self|string $method): self
    {
        return $method instanceof self ? $method : self::from(\strtoupper($method));
    }

    public static function values(): array
    {
        return \array_column(self::cases(), 'value');
    }
}
