<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\App\AppServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use Psr\Container\ContainerInterface;

#[Contract]
#[DefaultServiceProvider(AppServiceProvider::class)]
interface MutableContainer extends ContainerInterface
{
    /**
     * Add a new element to the container.
     *
     * @param string $id Identifier of the entry to add.
     * @param mixed $value Either the instance of the class or a Closure which creates an instance.
     */
    public function set(string $id, mixed $value): void;
}
