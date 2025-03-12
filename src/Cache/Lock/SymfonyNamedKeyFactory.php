<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Lock;

use PhoneBurner\SaltLite\Cache\CacheKey;
use PhoneBurner\SaltLite\Cache\Lock\NamedKey;
use PhoneBurner\SaltLite\Cache\Lock\NamedKeyFactory;
use PhoneBurner\SaltLite\Domain\Memory\Bytes;
use PhoneBurner\SaltLite\Serialization\Marshaller;
use PhoneBurner\SaltLite\String\Encoding\Encoding;

class SymfonyNamedKeyFactory implements NamedKeyFactory
{
    private array $cache = [];

    public function make(NamedKey|\Stringable|string $name): SymfonyNamedKey
    {
        $normalized_name = self::normalize($name);
        return $this->cache[self::normalize($name)] ??= match (true) {
            $name instanceof SymfonyNamedKey => $name,
            default => new SymfonyNamedKey($normalized_name),
        };
    }

    public function has(NamedKey|\Stringable|string $name): bool
    {
        return isset($this->cache[self::normalize($name)]);
    }

    public function delete(NamedKey|\Stringable|string $name): void
    {
        unset($this->cache[self::normalize($name)]);
    }

    private static function normalize(NamedKey|\Stringable|string $name): string
    {
        return $name instanceof NamedKey ? $name->name : CacheKey::make($name)->normalized;
    }

    /**
     * Transform a NamedKey object into a compressed base64-encoded string that
     * is safe for putting in a Beanstalkd job data payload.
     */
    public static function serialize(NamedKey $key): string
    {
        return Marshaller::serialize(
            value: $key,
            encoding: Encoding::Base64,
            use_compression: true,
            compression_threshold_bytes: new Bytes(0),
        );
    }

    /**
     * Transform a compressed base64-encoded string back into a NamedKey object.
     */
    public static function deserialize(string $key): SymfonyNamedKey
    {
        return Marshaller::deserialize(value: $key, encoding: Encoding::Base64);
    }
}
