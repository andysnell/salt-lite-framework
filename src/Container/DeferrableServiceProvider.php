<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface DeferrableServiceProvider extends ServiceProvider
{
    /**
     * Return a list of service class names that this service provider
     * provides. The service container will defer registering this provider
     * until (and only if) one of the provided services is requested.
     *
     * IMPORTANT: The array returned MUST include all entries registered by both
     * the bind() and register() methods!
     *
     * @return list<class-string>
     */
    public static function provides(): array;
}
