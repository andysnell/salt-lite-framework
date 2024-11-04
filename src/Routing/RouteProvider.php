<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing;

use PhoneBurner\SaltLite\Framework\Routing\Definition\Definition;

interface RouteProvider
{
    /**
     * @return array<Definition>
     */
    public function __invoke(): array;
}
