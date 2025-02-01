<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\Definition;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Routing\Route;
use PhoneBurner\SaltLite\Framework\Util\Helper\Enum;
use Psr\Http\Server\RequestHandlerInterface;

trait DefinitionBehaviour
{
    private array $attributes;
    private array $methods;
    private string $path;

    private function setAttributes(array $attributes): void
    {
        if (
            isset($attributes[Route::class])
            && ! \preg_match('#^(?:\\\[a-zA-Z]|[a-zA-Z])(?:[\-_.\\\]?[a-zA-Z0-9]+)*$#', (string)$attributes[Route::class])
        ) {
            throw new \InvalidArgumentException('invalid name: ' . $attributes[Route::class]);
        }

        if (
            isset($attributes[RequestHandlerInterface::class])
            && ! \is_a($attributes[RequestHandlerInterface::class], RequestHandlerInterface::class, true)
        ) {
            throw new \InvalidArgumentException('handler must be type of: ' . RequestHandlerInterface::class);
        }

        $this->attributes = $attributes;
    }

    private function setMethods(HttpMethod|string ...$methods): void
    {
        $this->methods = \array_values(\array_unique(Enum::values(...\array_map(HttpMethod::instance(...), $methods))));
    }

    public function with(callable ...$fns): self
    {
        return \array_reduce(
            $fns,
            static fn(self $definition, callable $fn): self => $fn($definition),
            $this,
        );
    }
}
