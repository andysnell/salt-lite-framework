<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareStack extends MiddlewareChain
{
    public static function make(RequestHandlerInterface $fallback_handler): self
    {
        return new self($fallback_handler);
    }

    #[\Override]
    protected function next(): MiddlewareInterface|null
    {
        return \array_pop($this->middleware_chain);
    }
}
