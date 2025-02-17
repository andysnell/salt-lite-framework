<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519Aes256Gcm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Blake2b;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Poly1305;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XSalsa20Poly1305;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Attribute\AlgorithmImplementation;
use PhoneBurner\SaltLite\Framework\Util\Helper\CaseAttr;

enum Algorithm
{
    /**
     * Diffie-Hellman key exchange over Curve25519 + XChaCha20 + Blake2b AEAD
     * The default choice until wider support for AEGIS-256 is available
     */
    #[AlgorithmImplementation(new X25519XChaCha20Blake2b())]
    case X25519XChaCha20Blake2b;

    /**
     * Diffie-Hellman key exchange over Curve25519 + XChaCha20 + Poly130 (IETF) AEAD
     */
    #[AlgorithmImplementation(new X25519XChaCha20Poly1305())]
    case X25519XChaCha20Poly1305;

    /**
     * Diffie-Hellman key exchange over Curve25519 + AES-256-GCM AEAD
     */
    #[AlgorithmImplementation(new X25519Aes256Gcm())]
    case X25519Aes256Gcm;

    /**
     * Diffie-Hellman key exchange over Curve25519 + XSalsa20 + Poly1305
     *
     * This is a non-AEAD construction, and is the algorithm used by the
     * sodium_crypto_box_* functions. This will have the widest compatibility
     * cross-platform, but is not as secure as the XChaCha20-Poly1305 or
     * XChaCha20-Blake2b
     */
    #[AlgorithmImplementation(new X25519XSalsa20Poly1305())]
    case X25519XSalsa20Poly1305;

    public function implementation(): EncryptionAlgorithm
    {
        $implementation = CaseAttr::first($this, AlgorithmImplementation::class)->algorithm ?? null;
        \assert($implementation instanceof EncryptionAlgorithm);
        return $implementation;
    }
}
