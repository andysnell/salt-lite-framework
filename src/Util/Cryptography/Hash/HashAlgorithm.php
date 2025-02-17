<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Attribute\HashAlgorithmProperties;

enum HashAlgorithm: string
{
    /**
     * BLAKE2b Cryptographic Hash and HMAC Algorithm
     *
     * Unlike the other algorithms here, which are available via the \hash_*
     * functions, we have to use the libsodium \sodium_crypto_generichash() functio
     *
     * @link https://www.rfc-editor.org/rfc/rfc7693.txt
     */
    #[HashAlgorithmProperties(digest_bytes: 32, cryptographic: true)]
    case BLAKE2B = 'blake2b';

    /**
     * xxHash Fast Hashing Function Family (Non-Cryptographic)
     *
     * @link https://xxhash.com/
     * @link https://php.watch/versions/8.1/xxHash
     */
    #[HashAlgorithmProperties(digest_bytes: 8, cryptographic: false)]
    case XXH3 = 'xxh3';

    #[HashAlgorithmProperties(digest_bytes: 4, cryptographic: false)]
    case XXH32 = 'xxh32';

    #[HashAlgorithmProperties(digest_bytes: 16, cryptographic: false)]
    case XXH128 = 'xxh128';

    /**
     * MurmurHash3 Hashing Function Family (Non-Cryptographic)
     *
     * @link https://en.wikipedia.org/wiki/MurmurHash
     * @link https://php.watch/versions/8.1/MurmurHash3
     */
    #[HashAlgorithmProperties(digest_bytes: 4, cryptographic: false)]
    case MURMUR3A = 'murmur3a';

    #[HashAlgorithmProperties(digest_bytes: 16, cryptographic: false)]
    case MURMUR3F = 'murmur3f';

    /**
     * SHA-3 Cryptographic Hash Function Family
     *
     * @link https://en.wikipedia.org/wiki/SHA-3
     */
    #[HashAlgorithmProperties(digest_bytes: 28, cryptographic: true)]
    case SHA3_224 = 'sha3-224';

    #[HashAlgorithmProperties(digest_bytes: 32, cryptographic: true)]
    case SHA3_256 = 'sha3-256';

    #[HashAlgorithmProperties(digest_bytes: 48, cryptographic: true)]
    case SHA3_384 = 'sha3-384';

    #[HashAlgorithmProperties(digest_bytes: 64, cryptographic: true)]
    case SHA3_512 = 'sha3-512';

    /**
     * SHA-2 Cryptographic Hash Function Family
     *
     * Note that PHP implements the FIPS version of SHA2-512/256, which has a
     * different set of initialization vectors from version used by libsodium and
     * elsewhere, which just returns the first 256-bits from the SHA-512 hash digest.
     *
     * @link https://en.wikipedia.org/wiki/SHA-2
     */
    #[HashAlgorithmProperties(digest_bytes: 28, cryptographic: true)]
    case SHA224 = 'sha224';

    #[HashAlgorithmProperties(digest_bytes: 32, cryptographic: true)]
    case SHA256 = 'sha256';

    #[HashAlgorithmProperties(digest_bytes: 48, cryptographic: true)]
    case SHA384 = 'sha384';

    #[HashAlgorithmProperties(digest_bytes: 64, cryptographic: true)]
    case SHA512 = 'sha512';

    #[HashAlgorithmProperties(digest_bytes: 28, cryptographic: true)]
    case SHA512_224 = 'sha512/224';

    #[HashAlgorithmProperties(digest_bytes: 32, cryptographic: true)]
    case SHA512_256_FIPS = 'sha512/256';

    /**
     * Cyclic Redundancy Check Family (Non-Cryptographic)
     *
     * @link https://en.wikipedia.org/wiki/Cyclic_redundancy_check
     */
    #[HashAlgorithmProperties(digest_bytes: 4, cryptographic: false)]
    case CRC32 = 'crc32';

    #[HashAlgorithmProperties(digest_bytes: 4, cryptographic: false)]
    case CRC32B = 'crc32b'; // version used by PHP crc32() function

    #[HashAlgorithmProperties(digest_bytes: 4, cryptographic: false)]
    case CRC32C = 'crc32c'; // aka "Castagnoli" version

    /**
     * Legacy "Broken" Algorithms
     */
    #[HashAlgorithmProperties(digest_bytes: 16, cryptographic: false, broken: true)]
    case MD5 = 'md5';

    #[HashAlgorithmProperties(digest_bytes: 20, cryptographic: false, broken: true)]
    case SHA1 = 'sha1';

    private const array DIGEST_SIZE_IN_BYTES = [
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
        self::SHA512_256_FIPS->name => 32,
        self::CRC32->name => 4,
        self::CRC32B->name => 4,
        self::CRC32C->name => 4,
        self::MD5->name => 16,
        self::SHA1->name => 20,
    ];

    private const array CRYPTOGRAPHIC = [
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
        self::SHA512_256_FIPS->name => true,
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
