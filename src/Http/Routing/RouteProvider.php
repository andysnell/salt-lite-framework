<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing;

use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\Definition;

interface RouteProvider
{
    /**
     * @return array<Definition>
     */
    public function __invoke(): array;
}
