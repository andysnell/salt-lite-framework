<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\Definition;

use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteDefinition;
use Traversable;

/**
 * @extends Traversable<RouteDefinition>
 */
interface DefinitionList extends Traversable
{
    public function hasNamedRoute(string $name): bool;

    public function getNamedRoute(string $name): RouteDefinition;
}
