<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing;

use PhoneBurner\SaltLite\Framework\Routing\Match\RouteMatch;

interface RouterResult
{
    public function isFound(): bool;

    public function getRouteMatch(): RouteMatch;
}
