<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NullableCast;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;

/**
 * Creates a PSR-6/PSR-16 safe cache key "namespaced" by the passed in parts
 *
 * eg. (string)CacheKey::make('user', 1, 'FooBarProfile') would return 'user.1.foo_bar_profile'
 */
#[Contract]
readonly class CacheKey implements \Stringable
{
    private const array RESERVED_CHARACTERS = [':', '{', '}', '(', ')', '/', '\\', '@'];

    public string $normalized;

    public function __construct(\Stringable|\BackedEnum|string|int ...$key_parts)
    {
        $this->normalized = \implode('.', \array_map(static function (\Stringable|\BackedEnum|string|int $part): string {
            $part = \implode('.', \array_map(static function (string $subpart): string {
                return Str::snake(\str_replace(self::RESERVED_CHARACTERS, '_', $subpart));
            }, \explode('.', \trim(NullableCast::string($part), '.'))));
            return $part !== '' ? $part : throw new \InvalidArgumentException('Cache key part cannot be empty string');
        }, $key_parts));
    }

    public static function make(\Stringable|\BackedEnum|string|int ...$key_parts): self
    {
        return new self(...$key_parts);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->normalized;
    }
}
