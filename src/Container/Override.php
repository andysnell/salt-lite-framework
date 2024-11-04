<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

final class Override
{
    public const string POSITION = 'position';
    public const string NAME = 'name';
    public const string HINT = 'type_hint';

    private mixed $value = null;

    public static function position(int $position): self
    {
        return new self(OverrideType::Position, $position);
    }

    public static function name(string $name): self
    {
        return new self(OverrideType::Name, $name);
    }

    public static function typeHint(string $type): self
    {
        return new self(OverrideType::Hint, $type);
    }

    private function __construct(
        private readonly OverrideType $type,
        private readonly string|int $identifier,
    ) {
    }

    public function with(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function is(mixed $type, mixed $identifier): bool
    {
        return $this->type === $type && $this->identifier === $identifier;
    }

    public function get(): mixed
    {
        return $this->value;
    }
}
