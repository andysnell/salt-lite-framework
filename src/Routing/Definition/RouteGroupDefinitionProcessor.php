<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Definition;

interface RouteGroupDefinitionProcessor
{
    public function __invoke(RouteGroupDefinition $definition): RouteGroupDefinition;
}
