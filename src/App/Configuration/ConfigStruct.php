<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Configuration;

/**
 * Implementations of this interface should be simple, final, and readonly,
 * "struct-like" objects containing just *public* property declarations, without
 * methods or "behavior" other than the two serialization related methods.
 * Essentially, this is intended to replace arbitrary associative arrays of configuration values type-safe structure.
 *
 * Implementations should be both final and readonly
 *
 * @todo Enforce that implementations are 'final' and 'readonly' via custom PHPStan rule
 * @todo Enforce allow-list of property types via custom PHPStan rule
 * @todo Add custom PHPStan rule to check that all declared properties are serialized/deserialized
 */
interface ConfigStruct
{
    public function __serialize(): array;

    public function __unserialize(array $data): void;
}
