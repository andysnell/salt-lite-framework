<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceContainer;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface ServiceFactory
{
    /**
     * @param class-string $id
     */
    public function __invoke(App $app, string $id): object;
}
