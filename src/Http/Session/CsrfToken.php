<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\FixedLengthBinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringFromRandomBytes;

/**
 * The session id is a 256-bit random string that is used to identify a CSRF token
 *
 * Note: this class is intentionally not readonly to allow for the sensitive token
 * value to be overwritten in memory when the object is destroyed.
 */
final class CsrfToken extends FixedLengthBinaryString
{
    use BinaryStringFromRandomBytes;

    public const int LENGTH = 32; // 256-bit string

    /**
     * The CSRF Token finds its way into a couple different places where having
     * non-url friendly characters, including trailing padding, would be a pain.
     */
    public const ConstantTimeEncoding DEFAULT_ENCODING = ConstantTimeEncoding::Base64UrlNoPadding;
}
