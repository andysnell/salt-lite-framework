<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Domain;

/**
 * Placeholder object to represent `null` values intentionally set in a PSR-11
 * container, in order to differentiate from `null` as a sentinel value for a
 * lookup miss.
 */
enum NullEntry
{
    case None;
}
