<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Message;

/**
 * Implementations of this interface must be invokable and serializable.
 *
 * This is the replacement for "jobs" in the old system. These are messages
 * that "know how to do their job" and combine a data structure (via
 * the constructor) with the behavior (via the __invoke method). Any arguments
 * defined on __invoke() will be resolved by the service container when the object
 * is invoked.
 */
interface InvokableMessage
{
}
