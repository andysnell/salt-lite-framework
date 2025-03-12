<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Type\Reflect;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

function app(): App
{
    return App::instance();
}

/**
 * Get an environment variable allowing for default.
 * Note: this has slightly different behavior from SALT, as it does not check $_SERVER
 * or fall back to getenv() if the variable is not set in $_ENV. The Salt-Lite
 * Framework assumes that all environment variables are set in $_ENV.
 */
function env(
    string $key,
    mixed $production = null,
    mixed $development = null,
    mixed $integration = null,
): mixed {
    return App::instance()->environment->env($key, $production, $development, $integration);
}

function stage(
    mixed $production = null,
    mixed $development = null,
    mixed $integration = null,
): mixed {
    return App::instance()->environment->match($production, $development, $integration);
}

/**
 * Get full path relative to the application root
 *
 * @return non-empty-string
 */
function path(string $path): string
{
    \assert(\defined('\PhoneBurner\SaltLite\Framework\APP_ROOT'), 'APP_ROOT must be defined');
    return APP_ROOT . $path;
}

/**
 * Use when you don't control the instantiation of the object, but have a factory
 * that can return an instance of the object, e.g. where you would normally not
 * (or cannot) just use "new" to create an instance.
 *
 * Note: this method wraps the passed factory in another closure that will make sure
 * that the object ultimately returned is not a lazy object. This is useful
 * when we do not necessarily know if the factory is going to give us something
 * that is lazy or not, e.g. If the factory is just resolving the class from
 * the PSR-11 container, for something like a database connection or entity,
 * the object returned by the container may be another ghost or proxy.
 *
 * @param callable(T): T $factory
 * @return T&object
 * @see Reflect::proxy() for a more complete implementation
 * @template T of object
 */
function proxy(callable $factory): object
{
    // we need to make sure that the factory is a \Closure that returns an instance
    // of the class that it is supposed to be creating. Using first class
    // callable syntax to should return the same instance if the
    // original initializer is already a \Closure and static.
    $factory = $factory(...);
    $initializer_reflection = new \ReflectionFunction($factory);
    \assert($initializer_reflection->getNumberOfParameters() === 1);

    /** @var class-string<T> $class */
    $class = (string)$initializer_reflection->getReturnType();
    \assert(\class_exists($class));

    $class_reflection = new \ReflectionClass($class);

    return $class_reflection->newLazyProxy(
        static fn(object $object): object => $class_reflection->initializeLazyObject($factory($object)),
    );
}

/**
 * Use when you control the instantiation of the object, e.g. where you would
 * normally use "new" to create an instance, passing in the object's dependencies.
 *
 * @see Reflect::ghost() for a more complete implementation
 * @template T of object
 * @param \Closure(T): void $initializer
 * @return T&object
 */
function ghost(\Closure $initializer): object
{
    // we need to make sure that the initializer is a \Closure that takes a single argument
    $initializer_reflection = new \ReflectionFunction($initializer);
    \assert($initializer_reflection->getNumberOfParameters() === 1);

    /** @var class-string<T> $class */
    $class = (string)$initializer_reflection->getParameters()[0]->getType();
    \assert(\class_exists($class));

    return new \ReflectionClass($class)->newLazyGhost($initializer);
}

function null_if_false(mixed $value): mixed
{
    return $value === false ? null : $value;
}
