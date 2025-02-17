<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Collections;

use PhoneBurner\SaltLite\Framework\Domain\Arrayable;

/**
 * Lists are collections of items that are stored in a specific order, cannot be
 * accessed by a key, and may contain duplicate values.
 *
 * Lists do not implement \ArrayAccess because they do not have keys, but they
 * do implement \Countable, \IteratorAggregate, and Arrayable.
 *
 * Note that while ListCollection do have lookup methods, as O(n) operations they
 * are not as efficient as the O(1) lookup methods of a MapCollection, which are
 * optimized for key-value pairs.
 *
 * @template TValue
 * @extends \IteratorAggregate<int, TValue>
 */
interface ListCollection extends \Countable, \IteratorAggregate, Arrayable
{
}
