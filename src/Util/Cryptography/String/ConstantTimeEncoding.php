<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\String;

use PhoneBurner\SaltLite\Framework\Util\Encoding;

/**
 * Constant-Time Encoding for Binary Strings
 *
 * A constant-time function is one that takes the same amount of time to execute
 * for any input of a given size. This is important for cryptographic operations
 * because it prevents timing attacks, where an attacker can learn information
 * about the input by measuring how long it takes to process it. All cryptographic
 * operations should use constant-time encoding and comparison functions (e.g.
 * \hash_equals()). This is especially important when working with input from
 * untrusted sources, such as user input or network traffic.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4648
 * @link https://paragonie.com/blog/2016/06/constant-time-encoding-boring-cryptography-rfc-4648-and-you
 * @see Encoding for the more performant encoding that can be used for non-sensitive data
 */
enum ConstantTimeEncoding
{
    case Hex;
    case Base64;
    case Base64NoPadding;
    case Base64Url;
    case Base64UrlNoPadding;

    public const string HEX_PREFIX = Encoding::HEX_PREFIX;
    public const string BASE64_PREFIX = Encoding::BASE64_PREFIX;
    public const string BASE64URL_PREFIX = Encoding::BASE64URL_PREFIX;

    public const string HEX_REGEX = Encoding::HEX_REGEX;
    public const string BASE64_REGEX = Encoding::BASE64_REGEX;
    public const string BASE64URL_REGEX = Encoding::BASE64URL_REGEX;
    public const string BASE64_NO_PADDING_REGEX = Encoding::BASE64_NO_PADDING_REGEX;
    public const string BASE64URL_NO_PADDING_REGEX = Encoding::BASE64URL_NO_PADDING_REGEX;

    /**
     * Note that we have to use a different prefix for base64url encoding, even
     * though we decode both base64 and base64url encoded strings in the same way
     * in order to be compliant with RFC 4648, which requires that the two encodings
     * not be conflated together as 'base64'.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4648#section-5
     */
    public function prefix(): string
    {
        return match ($this) {
            self::Hex => self::HEX_PREFIX,
            self::Base64, self::Base64NoPadding => self::BASE64_PREFIX,
            self::Base64Url, self::Base64UrlNoPadding => self::BASE64URL_PREFIX,
        };
    }

    public function regex(): string
    {
        return match ($this) {
            self::Hex => self::HEX_REGEX,
            self::Base64 => self::BASE64_REGEX,
            self::Base64NoPadding => self::BASE64_NO_PADDING_REGEX,
            self::Base64Url => self::BASE64URL_REGEX,
            self::Base64UrlNoPadding => self::BASE64URL_NO_PADDING_REGEX,
        };
    }

    /**
     * Constant-time encoding functions for binary strings.
     */
    public function encode(string $value, bool $prefix = false): string
    {
        return ($prefix ? $this->prefix() : '') . match ($this) {
            self::Hex => \sodium_bin2hex($value),
            self::Base64 => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_ORIGINAL),
            self::Base64NoPadding => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING),
            self::Base64Url => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_URLSAFE),
            self::Base64UrlNoPadding => \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
        };
    }

    /**
     * Constant-time decoding functions for binary strings.
     * We support decoding from any of the four base64 variants without needing to
     * know which variant is being used first. We also support decoding from hex
     * and base64 encoded strings with prefixes.
     */
    public function decode(string $value): string
    {
        return match ($this) {
            self::Hex => self::decodeFromHex($value) ?? throw new \UnexpectedValueException(
                'Invalid Hex Encoded String',
            ),
            self::Base64, self::Base64NoPadding => self::decodeFromBase64($value) ?? throw new \UnexpectedValueException(
                'Invalid Base64 Encoded String',
            ),
            self::Base64Url, self::Base64UrlNoPadding => self::decodeFromBase64($value) ?? throw new \UnexpectedValueException(
                'Invalid Base64Url Encoded String',
            ),
        };
    }

    public static function decodeFromHex(string $value): string|null
    {
        if (\str_starts_with($value, self::HEX_PREFIX)) {
            $value = \substr($value, 4);
        }

        if (\str_starts_with($value, '0x')) {
            $value = \substr($value, 2);
        }

        try {
            return \sodium_hex2bin($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Safely decode a string encoded in any of the four base64 variants without
     * needing to know which variant is being used first. We also safely handle
     *  extra padding characters that may have been added to the end of the string.
     */
    public static function decodeFromBase64(string $value): string|null
    {
        if (\str_starts_with($value, 'base64url:')) {
            $value = \substr($value, 10);
        } elseif (\str_starts_with($value, 'base64:')) {
            $value = \substr($value, 7);
        }

        try {
            // Replace URL-safe characters, trim trailing padding, and decode
            return \sodium_base642bin(
                \rtrim(\strtr($value, '-_', '+/'), '='),
                \SODIUM_BASE64_VARIANT_ORIGINAL_NO_PADDING,
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
