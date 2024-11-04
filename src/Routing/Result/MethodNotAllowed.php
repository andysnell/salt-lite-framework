<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Result;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Routing\Match\RouteMatch;
use PhoneBurner\SaltLite\Framework\Routing\RouterResult;

class MethodNotAllowed implements RouterResult
{
    /**
     * @var array<HttpMethod>
     */
    private readonly array $methods;

    public static function make(HttpMethod ...$methods): self
    {
        return new self(...$methods);
    }

    private function __construct(HttpMethod ...$methods)
    {
        $this->methods = $methods;
    }

    #[\Override]
    public function isFound(): bool
    {
        return false;
    }

    #[\Override]
    public function getRouteMatch(): RouteMatch
    {
        throw new \LogicException('match was not found');
    }

    /**
     * @return array<HttpMethod>
     */
    public function getAllowedMethods(): array
    {
        return $this->methods;
    }
}
