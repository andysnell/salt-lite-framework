<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Hash;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;

/**
 * Implementing classes MUST return the lower-case hexit representation of the
 * hash digest when the __toString() method is called.
 */
interface MessageDigest extends \Stringable
{
    public function algorithm(): HashAlgorithm;

    public function digest(Encoding $encoding = Encoding::Hex): string;
}
