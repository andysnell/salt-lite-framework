<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ParameterOverride;

final readonly class OverrideByParameterType implements ParameterOverride
{
    public function __construct(
        public string $type,
        public mixed $value = null,
    ) {
        $this->type !== '' || throw new \UnexpectedValueException(
            'overridden type hint identifier cannot be empty',
        );
    }

    public function type(): OverrideType
    {
        return OverrideType::Hint;
    }

    public function identifier(): string
    {
        return $this->type;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
