<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;

use function PhoneBurner\SaltLite\Framework\null_if_false;

/**
 * String Encoding Variants (RFC 4648)
 *
 * @link https://datatracker.ietf.org/doc/html/rfc4648
 * @see ConstantTimeEncoding for the encoding and decoding methods that should be used
 * when working with cryptographic binary strings, or any other sensitive data
 * that could be vulnerable to timing attacks.
 */
enum Encoding
{
    case Hex;
    case Base64;
    case Base64NoPadding;
    case Base64Url;
    case Base64UrlNoPadding;

    public const string HEX_PREFIX = 'hex:';
    public const string BASE64_PREFIX = 'base64:';
    public const string BASE64URL_PREFIX = 'base64url:';

    public const string HEX_REGEX = '/^[A-Fa-f0-9]+$/';
    public const string BASE64_REGEX = '/^[A-Za-z0-9+\/]+={0,2}$/';
    public const string BASE64URL_REGEX = '/^[A-Za-z0-9-_]+={0,2}$/';
    public const string BASE64_NO_PADDING_REGEX = '/^[A-Za-z0-9+\/]+$/';
    public const string BASE64URL_NO_PADDING_REGEX = '/^[A-Za-z0-9-_]+$/';

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
            self::Hex => \bin2hex($value),
            self::Base64 => \base64_encode($value),
            self::Base64NoPadding => \trim(\base64_encode($value), '='),
            self::Base64Url => \strtr(\base64_encode($value), '+/', '-_'),
            self::Base64UrlNoPadding => \trim(\strtr(\base64_encode($value), '+/', '-_'), '='),
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
            self::Hex => self::decodeHex($value) ?? throw new \UnexpectedValueException(
                'Invalid Hex Encoded String',
            ),
            self::Base64, self::Base64NoPadding => self::decodeBase64($value) ?? throw new \UnexpectedValueException(
                'Invalid Base64 Encoded String',
            ),
            self::Base64Url, self::Base64UrlNoPadding => self::decodeBase64($value) ?? throw new \UnexpectedValueException(
                'Invalid Base64Url Encoded String',
            ),
        };
    }

    public static function decodeHex(string $value): string|null
    {
        if (\str_starts_with($value, self::HEX_PREFIX)) {
            $value = \substr($value, 4);
        }

        if (\str_starts_with($value, '0x')) {
            $value = \substr($value, 2);
        }

        // Intentionally suppress the \E_WARNING that is also raised (in addition
        // to the boolean false return value) when the input is not a valid hex string
        return null_if_false(@\hex2bin($value));
    }

    /**
     * Safely decode a string encoded in any of the four base64 variants without
     * needing to know which variant is being used first. We also safely handle
     * extra padding characters that may have been added to the end of the string.
     */
    public static function decodeBase64(string $value): string|null
    {
        if (\str_starts_with($value, 'base64url:')) {
            $value = \substr($value, 10);
        } elseif (\str_starts_with($value, 'base64:')) {
            $value = \substr($value, 7);
        }

        // Replace URL-safe characters, and trim trailing padding)
        $value = \rtrim(\strtr($value, '-_', '+/'), '=');

        // Calculate the correct padding length based on the length of the input string
        return null_if_false(match (\strlen($value) % 4) {
            0 => \base64_decode(\strtr($value, '-_', '+/'), true),
            2 => \base64_decode(\strtr($value, '-_', '+/') . '==', true),
            3 => \base64_decode(\strtr($value, '-_', '+/') . '=', true),
            default => null, // Valid base64 strings cannot have a length that is 1 mod 4
        });
    }
}
