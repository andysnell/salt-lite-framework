<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ParameterOverride;

class OverrideCollection
{
    /**
     * @var array<name-of<OverrideType>, array<string|int, ParameterOverride>>
     */
    private readonly array $overrides;

    public function __construct(ParameterOverride ...$overrides)
    {
        $index = [];
        foreach ($overrides as $override) {
            $index[$override->type()->name][$override->identifier()] = $override;
        }

        $this->overrides = $index;
    }

    public function find(OverrideType $type, int|string $identifier): ParameterOverride|null
    {
        return $this->overrides === [] ? null : $this->overrides[$type->name][$identifier] ?? null;
    }

    public function has(OverrideType $type, int|string $identifier): bool
    {
        return isset($this->overrides[$type->name][$identifier]);
    }
}
