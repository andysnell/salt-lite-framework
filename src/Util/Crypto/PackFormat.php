<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto;

final readonly class PackFormat
{
    public const string STRING_NULL_PADDED = 'a'; // NULL-padded string, retains trailing null bytes
    public const string STRING_NULL_TRIMMED = 'Z'; // NULL-padded string, removes trailing NULL bytes.
    public const string STRING_SPACE_PADDED = 'A'; // SPACE-padded string, strippings all trailing ASCII whitespace

    public const string HEX_LOW = 'h'; // Hex string, low nibble first
    public const string HEX_HIGH = 'H'; // Hex string, high nibble first

    public const string CHAR_SIGNED = 'c'; // signed char
    public const string CHAR_UNSIGNED = 'C'; // unsigned char

    public const string INT_SIGNED_ME = 'i'; // signed integer (machine dependent size and byte order)
    public const string INT_UNSIGNED_ME = 'I'; // unsigned integer (machine dependent size and byte order)

    public const string INT16_SIGNED_ME = 's'; // signed short (always 16 bit, machine byte order)
    public const string INT16_UNSIGNED_ME = 'S'; // unsigned short (always 16 bit, machine byte order)
    public const string INT16_UNSIGNED_BE = 'n'; // unsigned short (always 16 bit, big endian byte order)
    public const string INT16_UNSIGNED_LE = 'v'; // unsigned short (always 16 bit, little endian byte order)

    public const string INT32_SIGNED_ME = 'l'; // signed long (always 32 bit, machine byte order)
    public const string INT32_UNSIGNED_ME = 'L'; // unsigned long (always 32 bit, machine byte order)
    public const string INT32_UNSIGNED_BE = 'N'; // unsigned long (always 32 bit, big endian byte order)
    public const string INT32_UNSIGNED_LE = 'V'; // unsigned long (always 32 bit, little endian byte order)

    public const string INT64_SIGNED_ME = 'q'; // signed long long (always 64 bit, machine byte order)
    public const string INT64_UNSIGNED_ME = 'Q'; // unsigned long long (always 64 bit, machine byte order)
    public const string INT64_UNSIGNED_BE = 'J'; // unsigned long long (always 64 bit, big endian byte order)
    public const string INT64_UNSIGNED_LE = 'P'; // unsigned long long (always 64 bit, little endian byte order)

    public const string FLOAT_ME = 'f'; // float (machine dependent size and representation)
    public const string FLOAT_LE = 'g'; // float (machine dependent size, little endian byte order)
    public const string FLOAT_BE = 'G'; // float (machine dependent size, big endian byte order)

    public const string DOUBLE_ME = 'd'; // double (machine dependent size and representation)
    public const string DOUBLE_LE = 'e'; // double (machine dependent size, little endian byte order)
    public const string DOUBLE_BE = 'E'; // double (machine dependent size, big endian byte order)

    public const string NULL_BYTE = 'x'; // NULL byte
    public const string BACK_UP = 'X'; // Back up one byte
    public const string NULL_FILL = '@'; // NULL-fill to absolute position

    public const string REPEAT_TO_END = '*'; // *
}
