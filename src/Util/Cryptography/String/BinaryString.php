<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\String;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;

/**
 * Represents a some kind of string in its raw binary form, something like a
 * cryptographic key, message signature, or ciphertext. Usually this would be
 * something where we are usually concerned with the raw bytes of the string and
 * not a human-readable representation. Encapsulating the raw bytes in a class
 * allows us to enforce type safety and avoid accidentally mixing up different
 * type of binary strings, or accidentally treating a binary string as a regular
 * string in a context where that would be inappropriate.
 */
interface BinaryString extends \Stringable, \JsonSerializable
{
    public const ConstantTimeEncoding DEFAULT_ENCODING = ConstantTimeEncoding::Base64Url;

    /**
     * @return string The raw binary string material
     */
    public function bytes(): string;

    /**
     * @return non-negative-int The length of the binary string in bytes
     */
    public function length(): int;

    /**
     * Create a new instance of the binary string from a hex-encoded string, or
     * a string encoded with one of the four base64 variants supported by
     * \sodium_bin2base64().
     *
     * Hex Encoding: "hex:0123456789abcdef" or "0x0123456789abcdef" or "0123456789abcdef"
     * - Implementations MUST ignore the "hex:" prefix for hex encoded strings
     * - Implementations MUST ignore leading "0x" characters
     * - Implementations MUST treat hex strings in a case-insensitive manner
     *
     * Base64/Base64Url Encoding: "base64:SGVsbG8gV29ybGQ=" or "base64url:SGVsbG8gV29ybGQ="
     * - Implementations MUST ignore the "base64:" and "base64url:" prefixes
     * - Implementations MUST ignore extra or missing trailing padding and calculate the correct
     * - Implementations MUST treat base64 and base64url as equivalent encodings
     *
     * Optionally, implementations with a fixed length MAY also enforce that the
     * decoded binary string match that length.
     */
    public static function import(
        #[\SensitiveParameter] string $string,
        ConstantTimeEncoding|null $encoding = null,
    ): static;

    /**
     * Return the binary string as an encoded string
     *
     * @param bool $prefix If true, the encoded string will be prefixed an
     * identifier for the encoding type, either "base64:", "base64url", or "hex:".
     */
    public function export(
        ConstantTimeEncoding|null $encoding = null,
        bool $prefix = false,
    ): string;

    /**
     * Follows the same rules as the import() method, but has a more open signature
     * and returns null instead of throwing an exception if the input string is
     * not a valid encoding.
     */
    public static function tryImport(
        #[\SensitiveParameter] string|null $string,
        ConstantTimeEncoding|null $encoding = null,
    ): static|null;
}
