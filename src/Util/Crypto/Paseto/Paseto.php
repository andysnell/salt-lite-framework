<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\Exception\PasetoCryptoException;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\Exception\PasetoLogicException;
use Stringable;

/**
 * PASETO (Platform Agnostic Security Tokens)
 *
 * PASETO is a specification for secure, stateless tokens, similar to JWT/JOSE.
 * Unlike JWT, which is designed around "algorithm agility" and flexibility, and
 * suffers from numerous security defects as a result, PASETO is specifically
 * designed to only allow secure operations and is restricted to a predefined
 * set of versioned protocols, each defining two complete algorithms for both
 * authenticated symmetric-key encryption of the payload ("local") and
 * public-key authentication of plaintext data ("public").
 *
 *
 * This implementation currently supports the following:
 * - V2 Local: XChaCha20-Poly1305 (192-bit nonce, 256-bit key, 128-bit authentication tag)
 * - V4 Public: Ed25519 (EdDSA over Curve25519) Public Key Signature
 *
 * Notes:
 * - Both algorithms can use the same original key string, as we use it to
 *   derive both a symmetric key and a keypair for producing public/secret keys.
 * - The specification requires that everything except the token header to be
 *   encoded (strictly) with Base64Url without padding. (https://tools.ietf.org/html/rfc4648#page-8)
 *
 * @link https://github.com/paseto-standard/paseto-spec
 * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Version2.md
 * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Version4.md
 * @link https://github.com/paragonie/paseto
 */
final readonly class Paseto implements Stringable
{
    private const int NONCE_BYTES = \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

    private function __construct(
        public string $version,
        public string $purpose,
        public string $payload,
        public string $footer = '',
    ) {
        if (! self::supported($this->version, $this->purpose)) {
            throw new PasetoLogicException(\sprintf("Unsupported Version/Purpose: %s.%s", $version, $purpose));
        }

        if ($this->payload === '') {
            throw new PasetoLogicException('Payload Cannot Be Empty');
        }
    }

    public static function parse(string $token): self
    {
        [$version, $purpose, $payload, $footer] = \explode('.', $token) + ['', '', '', ''];
        $payload = \sodium_base642bin($payload, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        $footer = \sodium_base642bin($footer, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);

        return new self($version, $purpose, $payload, $footer);
    }

    /**
     * Create a PASETO V2 Local (Authenticated Symmetric Encryption) Token
     *
     * @link https://github.com/paragonie/paseto/blob/v2.x/src/Protocol/Version2.php
     */
    public static function local(PasetoKey $key, PasetoMessage $message): self
    {
        $header = self::header(PasetoVersion::V2, PasetoPurpose::LOCAL);
        $nonce = \sodium_crypto_generichash($message->data, \random_bytes(self::NONCE_BYTES), self::NONCE_BYTES);
        $ciphertext = \sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $message->data,
            self::encode($header, $nonce, $message->footer),
            $nonce,
            $key->shared(),
        );

        return new self(PasetoVersion::V2, PasetoPurpose::LOCAL, $nonce . $ciphertext, $message->footer);
    }

    /**
     * Create a PASETO V4 Public (Public Key Signature Authentication) Token
     *
     * @link
     */
    public static function public(PasetoKey $key, PasetoMessage $message): self
    {
        $header = self::header(PasetoVersion::V4, PasetoPurpose::PUBLIC);
        $encoded = self::encode($header, $message->data, $message->footer, $message->implicit_claims);
        $signature = \sodium_crypto_sign_detached($encoded, $key->secret());

        return new self(PasetoVersion::V4, PasetoPurpose::PUBLIC, $message->data . $signature, $message->footer);
    }

    public function decode(PasetoKey $key, string $implicit = ''): PasetoMessage
    {
        if ($this->version === PasetoVersion::V2 && $this->purpose === PasetoPurpose::LOCAL) {
            return $this->decrypt($key);
        }

        if ($this->version === PasetoVersion::V4 && $this->purpose === PasetoPurpose::PUBLIC) {
            return $this->verify($key, $implicit);
        }

        throw new PasetoCryptoException('Unsupported Version/Purpose Combination');
    }

    #[\Override]
    public function __toString(): string
    {
        return \vsprintf('%s.%s.%s%s', [
            $this->version,
            $this->purpose,
            \sodium_bin2base64($this->payload, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            $this->footer ? '.' . \sodium_bin2base64($this->footer, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING) : '',
        ]);
    }

    private function verify(PasetoKey $key, string $implicit = ''): PasetoMessage
    {
        $header = $this->filterHeaderString(PasetoVersion::V4, PasetoPurpose::PUBLIC);
        $length = $this->filterPayloadLength(\SODIUM_CRYPTO_SIGN_BYTES);
        $data = \substr($this->payload, 0, $length - \SODIUM_CRYPTO_SIGN_BYTES);
        $signature = \substr($this->payload, $length - \SODIUM_CRYPTO_SIGN_BYTES);
        if (\strlen($signature) < \SODIUM_CRYPTO_SIGN_BYTES) {
            throw new PasetoCryptoException(' Invalid Token Signature Length');
        }

        $encoded = self::encode($header, $data, $this->footer, $implicit);
        if (\sodium_crypto_sign_verify_detached($signature, $encoded, $key->public())) {
            return new PasetoMessage($data, $this->footer, $implicit);
        }

        throw new PasetoCryptoException('Invalid Token Signature');
    }

    private function decrypt(PasetoKey $key): PasetoMessage
    {
        $header = $this->filterHeaderString(PasetoVersion::V2, PasetoPurpose::LOCAL);
        $length = $this->filterPayloadLength(self::NONCE_BYTES);
        $nonce = \substr($this->payload, 0, self::NONCE_BYTES);
        $data = \sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            \substr($this->payload, self::NONCE_BYTES, $length - self::NONCE_BYTES),
            self::encode($header, $nonce, $this->footer),
            $nonce,
            $key->shared(),
        );

        if ($data !== false) {
            return new PasetoMessage($data, $this->footer);
        }

        throw new PasetoCryptoException(' Invalid Token Signature');
    }

    /**
     * @phpstan-assert-if-true PasetoVersion::* $version
     * @phpstan-assert-if-true PasetoPurpose::* $purpose
     */
    private static function supported(string $version, string $purpose): bool
    {
        return ($version === PasetoVersion::V2 && $purpose === PasetoPurpose::LOCAL)
            || ($version === PasetoVersion::V4 && $purpose === PasetoPurpose::PUBLIC);
    }

    private function filterPayloadLength(int $minimum_length): int
    {
        $length = \strlen($this->payload);
        if ($length < $minimum_length) {
            throw new PasetoCryptoException(' Invalid Token Message Length');
        }

        return $length;
    }

    /**
     * @param PasetoVersion::*&string $version
     * @param PasetoPurpose::*&string $purpose
     */
    private function filterHeaderString(string $version, string $purpose): string
    {
        if ($this->version !== $version || $this->purpose !== $purpose) {
            throw new PasetoLogicException('Invalid Operation on Token');
        }

        return self::header($version, $purpose);
    }

    /**
     * @param PasetoVersion::*&string $version
     * @param PasetoPurpose::*&string $purpose
     */
    private static function header(string $version, string $purpose): string
    {
        return $version . '.' . $purpose . '.';
    }

    /**
     * Authentication Padding (PAE)
     *
     * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Common.md#authentication-padding
     * @return non-empty-string
     */
    private static function encode(string ...$parts): string
    {
        $accumulator = \pack('P', \count($parts) & \PHP_INT_MAX);
        foreach ($parts as $string) {
            $accumulator .= \pack('P', \strlen($string) & \PHP_INT_MAX);
            $accumulator .= $string;
        }

        if ($accumulator === '') {
            throw new PasetoLogicException('Accumulator String Cannot Be Empty');
        }

        return $accumulator;
    }
}
