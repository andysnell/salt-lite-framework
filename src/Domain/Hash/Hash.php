<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Hash;

use PhoneBurner\SaltLite\Framework\Domain\Hash\Exceptions\InvalidHash;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileReader;

readonly class Hash implements MessageDigest
{
    final private function __construct(public HashAlgorithm $algorithm, public string $digest)
    {
        if (! \ctype_xdigit($this->digest) || \strlen($this->digest) !== $algorithm->bytes() * 2) {
            throw new InvalidHash('Invalid Length or Character Set for ' . $algorithm->name);
        }
    }

    public static function make(
        string|\Stringable $digest,
        HashAlgorithm $algorithm = HashAlgorithm::XXH3,
    ): static {
        return new static($algorithm, \strtolower((string)$digest));
    }

    public static function string(
        string|\Stringable $content,
        HashAlgorithm $algorithm = HashAlgorithm::XXH3,
    ): self {
        return new self($algorithm, match ($algorithm) {
            HashAlgorithm::BLAKE2B => \bin2hex(\sodium_crypto_generichash((string)$content)),
            default => \hash($algorithm->value, (string)$content),
        });
    }

    public static function file(
        string|\Stringable $file,
        HashAlgorithm $algorithm = HashAlgorithm::XXH3,
    ): self {
        return match ($algorithm) {
            HashAlgorithm::BLAKE2B => self::iterable(FileReader::make($file), $algorithm),
            default => new self($algorithm, (string)\hash_file($algorithm->value, (string)$file)),
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
    public function digest(): string
    {
        return $this->digest;
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
        return $this->digest;
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    private static function sodiumPump(iterable $pump): self
    {
        $context = \sodium_crypto_generichash_init();
        self::sodiumPumpUpdate($context, $pump);

        return new self(HashAlgorithm::BLAKE2B, \bin2hex(\sodium_crypto_generichash_final($context)));
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

        return new self($algorithm, \hash_final($context));
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
