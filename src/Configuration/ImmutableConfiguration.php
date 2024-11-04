<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Configuration;

final readonly class ImmutableConfiguration implements Configuration
{
    public function __construct(public array $values = [])
    {
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Gets a configuration value by key (dot notation),
     * returning null if no value is set.
     */
    #[\Override]
    public function get(string $key): mixed
    {
        $key_parts = \explode('.', $key);
        return $this->values[$key] ?? match (\count($key_parts)) {
            1 => $this->values[$key_parts[0]] ?? null,
            2 => $this->values[$key_parts[0]][$key_parts[1]] ?? null,
            3 => $this->values[$key_parts[0]][$key_parts[1]][$key_parts[2]] ?? null,
            4 => $this->values[$key_parts[0]][$key_parts[1]][$key_parts[2]][$key_parts[3]] ?? null,
            5 => $this->values[$key_parts[0]][$key_parts[1]][$key_parts[2]][$key_parts[3]][$key_parts[4]] ?? null,
            default => (function (array $key_parts): mixed {
                $value = $this->values;
                foreach ($key_parts as $k) {
                    $value = \is_array($value) ? ($value[$k] ?? null) : null;
                }

                return $value;
            })($key_parts)
        };
    }
}
