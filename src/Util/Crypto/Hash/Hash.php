<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Hash;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Hash\Exceptions\InvalidHash;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Util;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileReader;

final readonly class Hash implements MessageDigest
{
    public string $digest;

    public function __construct(
        string $digest,
        public HashAlgorithm $algorithm = HashAlgorithm::XXH3,
        Encoding $encoding = Encoding::None,
    ) {
        try {
            $this->digest = Util::decode($encoding, $digest);
        } catch (\Exception $e) {
            throw new InvalidHash('Invalid Encoding for ' . $algorithm->name, previous: $e);
        }

        if (\strlen($this->digest) !== $algorithm->bytes()) {
            throw new InvalidHash('Invalid Length or Character Set for ' . $algorithm->name);
        }
    }

    /**
     * Create a new Hash instance from a digest string with the given algorithm and encoding.
     */
    public static function make(
        string|\Stringable $digest,
        HashAlgorithm $algorithm = HashAlgorithm::XXH3,
        Encoding $encoding = Encoding::Hex,
    ): self {
        return new self((string)$digest, $algorithm, $encoding);
    }

    public static function string(
        string|\Stringable $content,
        HashAlgorithm $algorithm = HashAlgorithm::XXH3,
    ): self {
        return new self(match ($algorithm) {
            HashAlgorithm::BLAKE2B => \sodium_crypto_generichash((string)$content),
            default => \hash($algorithm->value, (string)$content, true),
        }, $algorithm);
    }

    public static function file(
        string|\Stringable $file,
        HashAlgorithm $algorithm = HashAlgorithm::XXH3,
    ): self {
        return match ($algorithm) {
            HashAlgorithm::BLAKE2B => self::iterable(FileReader::make($file), $algorithm),
            default => new self((string)\hash_file($algorithm->value, (string)$file, true), $algorithm),
        };
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    public static function iterable(
        iterable $pump,
        HashAlgorithm $algorithm = HashAlgorithm::XXH3,
    ): self {
        return match ($algorithm) {
            HashAlgorithm::BLAKE2B => self::sodiumPump($pump),
            default => self::hashPump($algorithm, $pump),
        };
    }

    #[\Override]
    public function algorithm(): HashAlgorithm
    {
        return $this->algorithm;
    }

    #[\Override]
    public function digest(Encoding $encoding = Encoding::Hex): string
    {
        return Util::encode($encoding, $this->digest);
    }

    public function is(mixed $hash): bool
    {
        return $hash instanceof self
            && $this->algorithm === $hash->algorithm
            && \hash_equals($this->digest, $hash->digest);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->digest(Encoding::Hex);
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    private static function sodiumPump(iterable $pump): self
    {
        $context = \sodium_crypto_generichash_init();
        self::sodiumPumpUpdate($context, $pump);

        return new self(\sodium_crypto_generichash_final($context), HashAlgorithm::BLAKE2B);
    }

    /**
     * @param non-empty-string $context
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     * @phpstan-param-out non-empty-string $context
     */
    private static function sodiumPumpUpdate(string &$context, iterable $pump): void
    {
        foreach ($pump as $bucket) {
            if (\is_iterable($bucket)) {
                self::sodiumPumpUpdate($context, $bucket);
            } else {
                \assert($context !== '');
                \sodium_crypto_generichash_update($context, (string)$bucket);
            }
        }
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    private static function hashPump(HashAlgorithm $algorithm, iterable $pump): self
    {
        $context = \hash_init($algorithm->value);
        self::hashPumpUpdate($context, $pump);

        return new self(\hash_final($context, true), $algorithm);
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    private static function hashPumpUpdate(\HashContext $context, iterable $pump): void
    {
        foreach ($pump as $bucket) {
            if (\is_iterable($bucket)) {
                self::hashPumpUpdate($context, $bucket);
            } else {
                \hash_update($context, (string)$bucket);
            }
        }
    }
}
