<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\App\AppServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use Psr\Log\LoggerAwareInterface;

#[DefaultServiceProvider(AppServiceProvider::class)]
#[Contract]
interface ServiceContainer extends MutableContainer, InvokingContainer, LoggerAwareInterface
{
    /**
     * Returns true if :
     *  1) We already have a resolved entry for the $id
     *  2) We have a service factory that can resolve the entry
     *  3) A deferred service provider that can register an entry or service factory
     *
     * If the $strict parameter is false, it will also return true if:
     *  4) The $id string is a valid class-string for a class that we could potentially
     *     autowire, i.e., it is not an interface, trait, or abstract class.
     */
    public function has(string $id, bool $strict = false): bool;
}
