<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\Definition;

use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteGroupDefinition;

interface RouteGroupDefinitionProcessor
{
    public function __invoke(RouteGroupDefinition $definition): RouteGroupDefinition;
}
