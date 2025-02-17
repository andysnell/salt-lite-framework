<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;

/**
 * Implementing classes MUST return the lower-case hexit representation of the
 * hash digest when the __toString() method is called.
 */
interface MessageDigest extends \Stringable
{
    public function algorithm(): HashAlgorithm;

    public function digest(ConstantTimeEncoding $encoding = ConstantTimeEncoding::Hex): string;
}
