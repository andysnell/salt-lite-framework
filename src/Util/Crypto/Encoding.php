<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto;

enum Encoding
{
    case None;
    case Hex;
    case Base64;
    case Base64NoPadding;
    case Base64Url;
    case Base64UrlNoPadding;

    public const string BASE64_REGEX = '/^[A-Za-z0-9+\/]+={0,2}$/';
    public const string BASE64_URL_REGEX = '/^[A-Za-z0-9-_]+={0,2}$/';
    public const string BASE64_NO_PADDING_REGEX = '/^[A-Za-z0-9+\/]+$/';
    public const string BASE64_URL_NO_PADDING_REGEX = '/^[A-Za-z0-9-_]+$/';
    public const string HEX_REGEX = '/^[A-Fa-f0-9]+$/';

    /**
     * Try to detect the base64 variant of the encoded string. Unfortunately, this
     * is not a perfect solution, as we cannot tell the difference between a hex
     * string and a base64 string with a length that is a multiple of 2, as the
     * hex characters are a subset of the base64 characters.
     */
    public static function detectBase64Variant(string $encoded): self|null
    {
        return match (true) {
            \str_ends_with($encoded, '=') => match (true) {
                \str_contains($encoded, '-'), \str_contains($encoded, '_') => match (true) {
                    \preg_match(self::BASE64_URL_REGEX, $encoded) => self::Base64Url,
                    default => null
                },
                (bool)\preg_match(self::BASE64_REGEX, $encoded) => self::Base64,
                default => null
            },
            \str_contains($encoded, '-'), \str_contains($encoded, '_') => match (true) {
                \preg_match(self::BASE64_URL_NO_PADDING_REGEX, $encoded) => self::Base64Url,
                default => null
            },
            (bool)\preg_match(self::BASE64_NO_PADDING_REGEX, $encoded) => self::Base64NoPadding,
            default => null,
        };
    }

    public function isBase64Variant(): bool
    {
        return match ($this) {
            self::None, self::Hex => false,
            default => true,
        };
    }
}
