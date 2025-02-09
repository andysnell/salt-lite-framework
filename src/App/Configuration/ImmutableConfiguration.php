<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Configuration;

use PhoneBurner\SaltLite\Framework\App\Configuration\Configuration;

final readonly class ImmutableConfiguration implements Configuration
{
    public function __construct(public array $values = [])
    {
    }

    public function has(string $id): bool
    {
        return $this->get($id) !== null;
    }

    /**
     * Gets a configuration value by dot notation key, returning null if no value
     * is set. Note that while we fall back a recursive way to get the value, for
     * the sake of performance, we will try direct access first for up to 5 levels.
     * Notes:
     *  - We try to match the exact key string first before trying dot notation,
     *  - Keys containing dots, like URLs or email addresses are going to be
     *    problematic. It's ok to use these as values and as keys in nested arrays
     *    that are always accessed together.
     */
    #[\Override]
    public function get(string $id): mixed
    {
        $key_parts = \explode('.', $id);
        return $this->values[$id] ?? match (\count($key_parts)) {
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
