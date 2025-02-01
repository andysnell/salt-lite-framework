<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing;

use PhoneBurner\SaltLite\Framework\Http\Routing\Match\RouteMatch;

interface RouterResult
{
    public function isFound(): bool;

    public function getRouteMatch(): RouteMatch;
}
