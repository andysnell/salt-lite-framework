<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ParameterOverride;

final readonly class OverrideByParameterName implements ParameterOverride
{
    public OverrideType $type;

    public function __construct(
        public string $name,
        public mixed $value = null,
    ) {
        $this->type = OverrideType::Name;
        $this->name !== '' || throw new \UnexpectedValueException(
            'overridden parameter name identifier cannot be empty',
        );
    }

    public function type(): OverrideType
    {
        return OverrideType::Name;
    }

    public function identifier(): string
    {
        return $this->name;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
