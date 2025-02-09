<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie;

use PhoneBurner\SaltLite\Framework\App\Clock\Clock;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\Hmac;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\HmacKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Symmetric;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CookieManager
{
    public const string ENCRYPTED_COOKIE_PREFIX = 'slf.v1.base64.';

    /** @var array<QueuedCookie> */
    private array $queued_cookies = [];

    private readonly HmacKey $prefix_key;

    public function __construct(
        private readonly Symmetric $crypto,
        private readonly SharedKey $key,
        private readonly Clock $clock,
    ) {
        $this->prefix_key = HmacKey::derive($this->key, 'cookie_prefix');
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
            $prefix = Hmac::string($name, $this->prefix_key)->digest(Encoding::Base64NoPadding);
            $value = (string)$value;
            if (\str_starts_with($value, $prefix)) {
                try {
                    $cookies[$name] = $this->crypto->decrypt($this->key, \substr($value, \strlen($prefix)));
                } catch (\Throwable) { // Decrypt failed, unset the cookie and do not include in the request
                    $this->add(Cookie::remove($name));
                }
                continue;
            }

            $cookies[$name] = $value;
        }

        return $request->withCookieParams($cookies);
    }

    public function mutateResponse(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->queued_cookies as $queued_cookie) {
            $cookie = $queued_cookie->cookie;

            if ($queued_cookie->encrypt) {
                $prefix = Hmac::string($cookie->name, $this->prefix_key)->digest(Encoding::Base64NoPadding);
                $ciphertext = $this->crypto->encrypt($this->key, $cookie->value);
                $cookie = $cookie->withValue($prefix . $ciphertext);
            }

            $response = $response->withAddedHeader(HttpHeader::SET_COOKIE, $cookie->toString($this->clock));
        }

        return $response;
    }
}
