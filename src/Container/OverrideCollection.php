<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Container\Exception\OverriddenArgumentNotSet;

class OverrideCollection
{
    /**
     * @var array<Override>
     */
    private readonly array $overrides;

    /**
     * @param Override|array<Override> $overrides
     */
    public function __construct(array|Override $overrides = [])
    {
        $this->overrides = \is_object($overrides) ? [$overrides] : $overrides;
    }

    public function hasArgumentInPosition(int $position): bool
    {
        return $this->has(OverrideType::Position, $position);
    }

    public function getArgumentInPosition(int $position): mixed
    {
        return $this->get(OverrideType::Position, $position);
    }

    public function hasArgumentByName(string $name): bool
    {
        return $this->has(OverrideType::Name, $name);
    }

    public function getArgumentByName(string $name): mixed
    {
        return $this->get(OverrideType::Name, $name);
    }

    public function hasArgumentByTypeHint(string $type): bool
    {
        return $this->has(OverrideType::Hint, $type);
    }

    public function getArgumentByTypeHint(string $type): mixed
    {
        return $this->get(OverrideType::Hint, $type);
    }

    private function has(OverrideType $type, int|string $identifier): bool
    {
        foreach ($this->overrides as $override) {
            if ($override->is($type, $identifier)) {
                return true;
            }
        }

        return false;
    }

    private function get(OverrideType $type, int|string $identifier): mixed
    {
        foreach ($this->overrides as $override) {
            if ($override->is($type, $identifier)) {
                return $override->get();
            }
        }

        throw new OverriddenArgumentNotSet('Type `' . $type->name . '` Identifier `' . $identifier . '`');
    }
}
