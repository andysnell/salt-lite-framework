<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie;

use PhoneBurner\SaltLite\Framework\Domain\Time\TimeConstant;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Util\Clock\Clock;
use PhoneBurner\SaltLite\Framework\Util\Clock\SystemClock;
use Psr\Http\Message\ResponseInterface;

readonly class Cookie
{
    private const string RESERVED_CHARS_LIST = "=,; \t\r\n\v\f";
    private const array RESERVED_CHARS_FROM = ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"];
    private const array RESERVED_CHARS_TO = ['%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C'];

    public function __construct(
        public string $name,
        public string $value,
        public \DateTimeInterface|null $expires = null,
        public string $path = '/',
        public string $domain = '',
        public bool $secure = true,
        public bool $http_only = true,
        public SameSite|null $same_site = SameSite::Lax,
        public bool $partitioned = false,
        public bool $raw = false,
    ) {
        if ($name === '') {
            throw new \InvalidArgumentException('Cookie name cannot be empty');
        }

        if (\strpbrk($name, self::RESERVED_CHARS_LIST) !== false) {
            throw new \InvalidArgumentException(\sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if ($this->same_site === SameSite::None && $this->secure === false) {
            throw new \InvalidArgumentException('SameSite=None requires Secure Setting');
        }
    }

    public static function remove(
        string $name,
        string $path = '/',
        string $domain = '',
    ): self {
        return new self($name, '', null, $path, $domain);
    }

    public function withValue(string $value): self
    {
        return new self($this->name, $value, $this->expires, $this->path, $this->domain, $this->secure, $this->http_only, $this->same_site, $this->partitioned, $this->raw);
    }

    /**
     * This method added for convenience to set a one-off cookie on a response;
     * however, using the CookieManager to queue up and set cookies is preferred,
     * and safer, as it will handle encryption and decryption of cookies, as well
     * as preventing the loss of the cookie if a different response is returned
     * later in the middleware queue.
     */
    public function set(ResponseInterface $response, Clock $clock = new SystemClock()): ResponseInterface
    {
        return $response->withAddedHeader(HttpHeader::SET_COOKIE, $this->toString($clock));
    }

    public function toString(Clock $clock = new SystemClock()): string
    {
        $cookie = $this->raw ? $this->name : \str_replace(self::RESERVED_CHARS_FROM, self::RESERVED_CHARS_TO, $this->name);
        $cookie .= '=' . match (true) {
            $this->value === '' => \vsprintf('deleted; Expires=%s; Max-Age=0', [
                \gmdate(\DATE_RFC7231, $clock->now()->getTimestamp() - TimeConstant::MIN_SECONDS_IN_YEAR),
            ]),
            $this->expires instanceof \DateTimeInterface => \vsprintf('%s; Expires=%s; Max-Age=%d', [
                $this->raw ? $this->value : \rawurlencode($this->value),
                \gmdate(\DATE_RFC7231, $this->expires->getTimestamp()),
                \max(0, $this->expires->getTimestamp() - $clock->now()->getTimestamp()),
            ]),
            default => $this->raw ? $this->value : \rawurlencode($this->value),
        };

        if ($this->path) {
            $cookie .= '; Path=' . $this->path;
        }

        if ($this->domain) {
            $cookie .= '; Domain=' . $this->domain;
        }

        if ($this->secure) {
            $cookie .= '; Secure';
        }

        if ($this->http_only) {
            $cookie .= '; HttpOnly';
        }

        if ($this->same_site) {
            $cookie .= '; SameSite=' . $this->same_site->name;
        }

        if ($this->partitioned) {
            $cookie .= '; Partitioned';
        }

        return $cookie;
    }
}
