<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\CryptoRuntimeException;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\SerializationProhibited;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\Hash;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\HashAlgorithm;

/**
 * Base class for all 256-bit cryptographic keys.
 *
 * Note: this class and its children are is intentionally not readonly, as this
 * allows us to explicitly zero out the key in memory when the object is destroyed.
 */
abstract class BaseKey implements Key
{
    private const string ERROR_TEMPLATE = 'Key Must Be a String of Exactly %s Bytes. Got %s';

    /**
     * @var non-empty-string
     */
    protected string $value;

    abstract public function bytes(): string;

    /**
     * @return int<1, max>
     */
    abstract public static function length(): int;

    final public function __construct(#[\SensitiveParameter] string $value)
    {
        $length = static::length();
        $this->value = match (true) {
            \strlen($value) === $length => $value,
            \str_starts_with($value, 'base64:') => Util::decode(Encoding::Base64, \substr($value, 7)),
            \ctype_xdigit($value) && \strlen($value) === $length * 2 => Util::decode(Encoding::Hex, $value),
            default => throw new CryptoRuntimeException('Could Not Decode Key'),
        } ?: throw new CryptoRuntimeException('Key Cannot Be Empty');

        if (\strlen($this->value) !== $length) {
            throw new CryptoRuntimeException(\sprintf(self::ERROR_TEMPLATE, $length, \strlen($value)));
        }
    }

    final public function id(): string
    {
        return 'sha256:' . Hash::string($this->value, HashAlgorithm::SHA256);
    }

    final public function encoded(Encoding $encoding = Encoding::Base64): string
    {
        return match ($encoding) {
            Encoding::None => $this->value,
            Encoding::Hex => Util::encode(Encoding::Hex, $this->value),
            default => 'base64:' . Util::encode($encoding, $this->value)
        };
    }

    /**
     * Zero out the key in memory when the object is destroyed.
     */
    final public function __destruct()
    {
        Util::memzero($this->value);
    }

    public static function generate(): static
    {
        return new static(\random_bytes(static::length()));
    }

    /**
     * Create a derived key from another key and a salt, using HKDF-BLAKE2b.
     */
    public static function derive(Key $key, string $hkdf_info, string $salt = ''): static
    {
        return new static(Util::hkdf($key, $hkdf_info, $salt, static::length()));
    }

    final public function __serialize(): never
    {
        throw new SerializationProhibited();
    }

    final public function __unserialize(array $data): never
    {
        throw new SerializationProhibited();
    }
}
