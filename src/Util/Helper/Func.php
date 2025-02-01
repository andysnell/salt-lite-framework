<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

abstract readonly class Func
{
    /**
     * If the $value argument is callable, call it with the parameters passed in
     * the $args array, otherwise, just pass through the value unchanged.
     */
    final public static function value(mixed $value, mixed ...$args): mixed
    {
        return \is_callable($value) ? \call_user_func_array($value, $args) : $value;
    }

    /**
     * Provides a convenient way to produce a callback that calls a method on an
     * object that is passed to that callback. For example, when performing a
     * simple mapping operation over an iterable of objects, and just returning
     * the return value of a member method on those objects.
     *
     * Example 0:
     * Old: \array_map(fn(CarbonImmutable $datetime) => $datetime->getTimestamp(), $dates);
     * New: \array_map(Func::fwd('getTimestamp'), $dates);
     *
     * Example 1:
     * Old: \array_map(fn(CarbonImmutable $datetime) => $datetime->format('Y-m-d'), $dates);
     * New: \array_map(Func::fwd('getTimestamp', 'Y-m-d'), $dates);
     */
    final public static function fwd(string $method, mixed ...$args): \Closure
    {
        return static fn(object $subject) => $subject->{$method}(...$args);
    }

    final public static function noop(): \Closure
    {
        return static function (): void {
        };
    }
}
