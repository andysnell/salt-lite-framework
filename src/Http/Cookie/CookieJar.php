<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie;

use PhoneBurner\SaltLite\Framework\App\Clock\Clock;
use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Traversable;

/**
 * @implements \IteratorAggregate<QueuedCookie>
 */
class CookieJar implements ContainerInterface, \Countable, \IteratorAggregate
{
    /** @var array<QueuedCookie> */
    private array $queued_cookies = [];

    public function __construct(
        private readonly CookieEncrypter $cookie_encrypter,
        private readonly Clock $clock,
    ) {
    }

    /** @return array<QueuedCookie> */
    public function queue(): array
    {
        return $this->queued_cookies;
    }

    public function add(Cookie $cookie, bool $encrypt = false): void
    {
        $this->queued_cookies[$cookie->name] = new QueuedCookie($cookie, $encrypt);
    }

    public function mutateRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $cookies = [];
        foreach ($request->getCookieParams() as $name => $value) {
            // shortcut any value strings that are shorter than the minimum size when encrypted
            if (\strlen((string)$value) >= CookieEncrypter::MIN_BYTES) {
                $value = $this->cookie_encrypter->decrypt($name, $value);
            }

            // If decryption failed, unset the cookie and do not include it in the request
            if ($value === null) {
                $this->add(Cookie::remove($name));
                continue;
            }

            $cookies[$name] = $value;
        }

        return $request->withCookieParams($cookies);
    }

    public function mutateResponse(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->queued_cookies as $queued_cookie) {
            $response = $response->withAddedHeader(HttpHeader::SET_COOKIE, (match ($queued_cookie->encrypt) {
                true => $this->cookie_encrypter->encrypt($queued_cookie->cookie),
                false => $queued_cookie->cookie,
            })->toString($this->clock));
        }

        return $response;
    }

    public function get(string $id): QueuedCookie
    {
        return $this->queued_cookies[$id] ?? throw new NotFound();
    }

    public function has(string $id): bool
    {
        return isset($this->queued_cookies[$id]);
    }

    public function getIterator(): Traversable
    {
        yield from $this->queued_cookies;
    }

    public function count(): int
    {
        return \count($this->queued_cookies);
    }
}
