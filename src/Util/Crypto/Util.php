<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto;

class Util
{
    /**
     * Constant-Time encoding functions for binary strings.
     */
    public static function encode(Encoding $encoding, string $value): string
    {
        return match ($encoding) {
            Encoding::None => $value,
            Encoding::Hex => \sodium_bin2hex($value),
            Encoding::Base64 => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_ORIGINAL),
            Encoding::Base64NoPadding => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING),
            Encoding::Base64Url => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_URLSAFE),
            Encoding::Base64UrlNoPadding => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
        };
    }

    /**
     * Constant-Time encoding functions for binary strings.
     * We'll try to detect the base64 variant of the encoded string, if any of
     * the base64 variants are passed in as the encoding.
     */
    public static function decode(Encoding $encoding, string $value): string
    {
//        $encoding = match ($encoding) {
//            Encoding::None => Encoding::None,
//            Encoding::Hex => Encoding::Hex,
//            Encoding::Base64,
//            Encoding::Base64NoPadding,
//            Encoding::Base64Url,
//            Encoding::Base64UrlNoPadding => Encoding::detectBase64Variant($value) ?? ,
//        };

        return match ($encoding) {
            Encoding::None => $value,
            Encoding::Hex => \sodium_hex2bin($value),
            Encoding::Base64 => \sodium_base642bin($value, \SODIUM_BASE64_VARIANT_ORIGINAL),
            Encoding::Base64NoPadding => \sodium_base642bin($value, \SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING),
            Encoding::Base64Url => \sodium_base642bin($value, \SODIUM_BASE64_VARIANT_URLSAFE),
            Encoding::Base64UrlNoPadding => \sodium_base642bin($value, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
        };
    }

    /**
     * Pre-Authentication Encoding
     *
     * Before passing a message to a MAC function, the message must be encoded
     * in a specific way, to prevent certain types of attacks. This encoding is
     * called Pre-Authentication Encoding (PAE). The PAE string is constructed
     * as follows:
     * - The number of parts (as a 64-bit little-endian integer)
     * - For each part:
     *   - The length of the part (as a 64-bit little-endian integer)
     *   - The part itself
     *
     * The PAE string can then be passed to the MAC function as the message. This
     * makes it impossible for an attacker to create a collision with only a
     * partially controlled plaintext or creating an integer overflow.
     *
     * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Common.md#authentication-padding
     * @return non-empty-string
     **/
    public static function pae(string ...$parts): string
    {
        $accumulator = \pack(PackFormat::INT64_UNSIGNED_LE, \count($parts) & \PHP_INT_MAX);
        foreach ($parts as $string) {
            $accumulator .= \pack(PackFormat::INT64_UNSIGNED_LE, \strlen($string) & \PHP_INT_MAX);
            $accumulator .= $string;
        }

        return $accumulator ?: throw new \LogicException('Accumulator String Cannot Be Empty');
    }

    /**
     * Wrapper around the frequently used \sodium_crypto_generichash() function.
     */
    public static function hash(
        string $message,
        string $key = '',
        int $length = \SODIUM_CRYPTO_GENERICHASH_BYTES,
    ): string {
        return \sodium_crypto_generichash($message, $key, $length);
    }

    /**
     * HKDF-BLAKE2b
     *
     * The HKDF key derivation function is used to derive multiple keys from a
     * single master key. This is useful when you need to derive separate keys
     * for encryption and authentication from a single key. The HKDF function
     * uses a salt to prevent certain types of attacks, and a context string to
     * differentiate between different uses of the same key.
     *
     * @link https://github.com/paragonie/halite/blob/master/src/Util.php for
     * the reference implementation this is based on.
     */
    public static function hkdf(
        Key $key,
        string $info = '',
        string $salt = '',
        int $length = \SODIUM_CRYPTO_GENERICHASH_BYTES,
    ): string {
        return \sodium_crypto_generichash(
            $info . $salt . "\x01",
            \sodium_crypto_generichash(
                $key->bytes(),
                \str_repeat("\x00", \SODIUM_CRYPTO_GENERICHASH_KEYBYTES),
                \SODIUM_CRYPTO_GENERICHASH_KEYBYTES,
            ),
            $length,
        );
    }

    /**
     * Try to zero out sensitive data from memory, while playing nice with variables
     * that might not exist or might not be strings.
     */
    public static function memzero(mixed &...$value): void
    {
        foreach ($value as &$v) {
            if (isset($v)) {
                try {
                    if (\is_string($v)) {
                        \sodium_memzero($v);
                    }
                } finally {
                    unset($v);
                }
            }
        }
    }
}
