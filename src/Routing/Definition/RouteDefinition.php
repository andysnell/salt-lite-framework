<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Definition;

use JsonSerializable;
use Laminas\Diactoros\Uri;
use PhoneBurner\Http\Message\UriWrapper;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Routing\Domain\StaticFile;
use PhoneBurner\SaltLite\Framework\Routing\RequestHandler\RedirectRequestHandler;
use PhoneBurner\SaltLite\Framework\Routing\RequestHandler\StaticFileRequestHandler;
use PhoneBurner\SaltLite\Framework\Routing\Route;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteDefinition implements Route, Definition, JsonSerializable
{
    use UriWrapper;
    use DefinitionBehaviour;

    private array $params = [];

    /**
     * @param iterable<HttpMethod|HttpMethod::*> $methods
     * @param iterable<string,mixed> $attributes
     */
    public static function make(string $path, iterable $methods = [], iterable $attributes = []): self
    {
        return new self($path, Arr::wrap($methods), Arr::wrap($attributes));
    }

    /**
     * @param iterable<string,mixed> $attributes
     */
    public static function all(string $path, iterable $attributes = []): self
    {
        return self::make($path, HttpMethod::cases(), $attributes);
    }

    /**
     * @param iterable<string,mixed> $attributes
     */
    public static function get(string $path, iterable $attributes = []): self
    {
        return self::make($path, [HttpMethod::Get], $attributes);
    }

    /**
     * @param iterable<string,mixed> $attributes
     */
    public static function head(string $path, iterable $attributes = []): self
    {
        return self::make($path, [HttpMethod::Head], $attributes);
    }

    /**
     * @param iterable<string,mixed> $attributes
     */
    public static function post(string $path, iterable $attributes = []): self
    {
        return self::make($path, [HttpMethod::Post], $attributes);
    }

    /**
     * @param iterable<string,mixed> $attributes
     */
    public static function put(string $path, iterable $attributes = []): self
    {
        return self::make($path, [HttpMethod::Put], $attributes);
    }

    /**
     * @param iterable<string,mixed> $attributes
     */
    public static function patch(string $path, iterable $attributes = []): self
    {
        return self::make($path, [HttpMethod::Patch], $attributes);
    }

    /**
     * @param iterable<string,mixed> $attributes
     */
    public static function delete(string $path, iterable $attributes = []): self
    {
        return self::make($path, [HttpMethod::Delete], $attributes);
    }

    public static function file(string $path, StaticFile $file): self
    {
        return self::get($path)
            ->withHandler(StaticFileRequestHandler::class)
            ->withAttribute(StaticFile::class, $file);
    }

    public static function download(string $path, StaticFile $file): self
    {
        return self::file($path, $file)
            ->withAttribute(HttpHeader::CONTENT_DISPOSITION, 'attachment');
    }

    public static function redirect(string $path, string $uri, int $status = HttpStatus::PERMANENT_REDIRECT): self
    {
        return self::all($path)
            ->withHandler(RedirectRequestHandler::class)
            ->withAttribute(RedirectRequestHandler::URI, $uri)
            ->withAttribute(RedirectRequestHandler::STATUS_CODE, $status);
    }

    private function __construct(string $path, array $methods, array $attributes)
    {
        $this->path = $path;

        $this->setMethods(...\array_values($methods));
        $this->setAttributes($attributes);

        $this->syncUri();
    }

    public function getRoutePath(): string
    {
        return $this->path;
    }

    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    private function syncUri(): void
    {
        $this->setWrapped(new Uri(
            (string)(new UriTemplate($this->path))->render($this->params),
        ));
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'path' => $this->path,
            'methods' => $this->methods,
            'attributes' => $this->attributes,
        ];
    }

    #[\Override]
    public function __serialize(): array
    {
        return [
            'path' => $this->path,
            'methods' => $this->methods,
            'attributes' => $this->attributes,
        ];
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->path = $data['path'];
        $this->methods = $data['methods'];
        $this->attributes = $data['attributes'];

        $this->syncUri();
    }

    #[\Override]
    public function withPathParameter(string $name, string $value): self
    {
        return $this->withPathParameters(\array_merge($this->params, [
            $name => $value,
        ]));
    }

    #[\Override]
    public function withPathParameters(array $params): self
    {
        $new = new self($this->path, $this->methods, $this->attributes);
        $new->params = $params;
        $new->syncUri();

        return $new;
    }

    #[\Override]
    public function withRoutePath(string $path): self
    {
        return new self($path, $this->methods, $this->attributes);
    }

    #[\Override]
    public function withMethod(HttpMethod ...$method): self
    {
        $method = \array_map(HttpMethod::instance(...), $method);
        return new self($this->path, $method, $this->attributes);
    }

    #[\Override]
    public function withAddedMethod(HttpMethod ...$method): self
    {
        return new self($this->path, [...$method, ...$this->methods], $this->attributes);
    }

    #[\Override]
    public function withName(string $name): self
    {
        return $this->withAttribute(Route::class, $name);
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler_class
     */
    #[\Override]
    public function withHandler(string $handler_class): self
    {
        return $this->withAttribute(RequestHandlerInterface::class, $handler_class);
    }

    /**
     * @param class-string<MiddlewareInterface> ...$middleware
     */
    #[\Override]
    public function withMiddleware(string ...$middleware): self
    {
        return $this->withAttribute(MiddlewareInterface::class, $middleware);
    }

    #[\Override]
    public function withAttribute(string $name, mixed $value): self
    {
        return $this->withAttributes(\array_merge($this->attributes, [
            $name => $value,
        ]));
    }

    #[\Override]
    public function withAttributes(array $attributes): self
    {
        return new self(
            $this->path,
            $this->methods,
            $attributes,
        );
    }

    #[\Override]
    public function withAddedAttributes(array $attributes): self
    {
        return new self(
            $this->path,
            $this->methods,
            [...$this->attributes, ...$attributes],
        );
    }

    #[\Override]
    protected function wrap(UriInterface $uri): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withScheme(string $scheme): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withUserInfo(string $user, string|null $password = null): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withHost(string $host): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withPort(int|null $port): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withPath(string $path): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withQuery(string $query): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withFragment(string $fragment): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }
}
