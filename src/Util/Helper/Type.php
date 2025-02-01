<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

abstract readonly class Type
{
    /**
     * @template T of object
     * @param class-string<T> $type
     * @return T&object
     */
    final public static function of(string $type, object $value): object
    {
        return $value instanceof $type ? $value : throw new \UnexpectedValueException(
            \sprintf('Expected an instance of %s, but got %s', $type, $value::class),
        );
    }
}
