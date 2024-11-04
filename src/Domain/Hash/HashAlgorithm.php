<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Hash;

enum HashAlgorithm: string
{
    /**
     * BLAKE2b Cryptographic Hash and HMAC Algorithm
     *
     * @link https://www.rfc-editor.org/rfc/rfc7693.txt
     */
    case BLAKE2B = 'blake2b';

    /**
     * xxHash Fast Hashing Function Family (Non-Cryptographic)
     *
     * @link https://xxhash.com/
     * @link https://php.watch/versions/8.1/xxHash
     */
    case XXH3 = 'xxh3';
    case XXH32 = 'xxh32';
    case XXH128 = 'xxh128';

    /**
     * MurmurHash3 Hashing Function Family (Non-Cryptographic)
     *
     * @link https://en.wikipedia.org/wiki/MurmurHash
     * @link https://php.watch/versions/8.1/MurmurHash3
     */
    case MURMUR3A = 'murmur3a';
    case MURMUR3F = 'murmur3f';

    /**
     * SHA-3 Cryptographic Hash Function Family
     *
     * @link https://en.wikipedia.org/wiki/SHA-3
     */
    case SHA3_224 = 'sha3-224';
    case SHA3_256 = 'sha3-256';
    case SHA3_384 = 'sha3-384';
    case SHA3_512 = 'sha3-512';

    /**
     * SHA-2 Cryptographic Hash Function Family
     *
     * @link https://en.wikipedia.org/wiki/SHA-2
     */
    case SHA224 = 'sha224';
    case SHA256 = 'sha256';
    case SHA384 = 'sha384';
    case SHA512 = 'sha512';
    case SHA512_224 = 'sha512/224';
    case SHA512_256 = 'sha512/256';

    /**
     * Cyclic Redundancy Check Family (Non-Cryptographic)
     *
     * @link https://en.wikipedia.org/wiki/Cyclic_redundancy_check
     */
    case CRC32 = 'crc32';
    case CRC32B = 'crc32b'; // version used by PHP crc32() function
    case CRC32C = 'crc32c'; // aka "Castagnoli" version

    /**
     * Legacy "Broken" Algorithms
     */
    case MD5 = 'md5';
    case SHA1 = 'sha1';

    private const DIGEST_SIZE_IN_BYTES = [
        self::BLAKE2B->name => 32,
        self::XXH3->name => 8,
        self::XXH32->name => 4,
        self::XXH128->name => 16,
        self::MURMUR3A->name => 4,
        self::MURMUR3F->name => 16,
        self::SHA3_224->name => 28,
        self::SHA3_256->name => 32,
        self::SHA3_384->name => 48,
        self::SHA3_512->name => 64,
        self::SHA224->name => 28,
        self::SHA256->name => 32,
        self::SHA384->name => 48,
        self::SHA512->name => 64,
        self::SHA512_224->name => 28,
        self::SHA512_256->name => 32,
        self::CRC32->name => 4,
        self::CRC32B->name => 4,
        self::CRC32C->name => 4,
        self::MD5->name => 16,
        self::SHA1->name => 20,
    ];

    private const CRYPTOGRAPHIC = [
        self::BLAKE2B->name => true,
        self::XXH3->name => false,
        self::XXH32->name => false,
        self::XXH128->name => false,
        self::MURMUR3A->name => false,
        self::MURMUR3F->name => false,
        self::SHA3_224->name => true,
        self::SHA3_256->name => true,
        self::SHA3_384->name => true,
        self::SHA3_512->name => true,
        self::SHA224->name => true,
        self::SHA256->name => true,
        self::SHA384->name => true,
        self::SHA512->name => true,
        self::SHA512_224->name => true,
        self::SHA512_256->name => true,
        self::CRC32->name => false,
        self::CRC32B->name => false,
        self::CRC32C->name => false,
        self::MD5->name => false,
        self::SHA1->name => false,
    ];

    public static function default(bool $cryptographic = true): self
    {
        return $cryptographic ? self::BLAKE2B : self::XXH3;
    }

    public function bytes(): int
    {
        return self::DIGEST_SIZE_IN_BYTES[$this->name] ?? throw new \LogicException("Undefined Digest Size");
    }

    public function cryptographic(): bool
    {
        return self::CRYPTOGRAPHIC[$this->name] ?? throw new \LogicException("Undefined Hash Type");
    }
}
