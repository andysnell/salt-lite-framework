<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain;

readonly class RegExp implements \Stringable
{
    public function __construct(
        public string $expression,
        public string $modifiers = '',
    ) {
    }

    public static function make(string $expression, string $modifiers = ''): self
    {
        return new self($expression, $modifiers);
    }

    #[\Override]
    public function __toString(): string
    {
        return \sprintf('/%s/%s', $this->expression, $this->modifiers);
    }
}
