<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Contract]
final class MiddlewareQueue extends MiddlewareChain
{
    public static function make(
        RequestHandlerInterface $fallback_handler,
        EventDispatcherInterface|null $event_dispatcher = null,
    ): self {
        return new self($fallback_handler, $event_dispatcher);
    }

    #[\Override]
    protected function next(): MiddlewareInterface|null
    {
        return \array_shift($this->middleware_chain);
    }
}
