<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Attribute;

use PhoneBurner\SaltLite\Framework\Util\ClassString;

/**
 * @template T of object
 */
interface MapsToClassString
{
    /**
     * @return ClassString<T>
     */
    public function mapsTo(): ClassString;
}
