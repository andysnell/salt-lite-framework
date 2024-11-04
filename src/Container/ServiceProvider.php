<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface ServiceProvider
{
    /**
     * Register application services with the container. This step should not
     * have side effects, and should only bind service definitions to the container.
     */
    public function register(MutableContainer $container): void;
}
