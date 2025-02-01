<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Routing\Command\CommandHelper;

use PhoneBurner\SaltLite\Framework\Http\Routing\Command\CommandHelper\RouteDefinitionListSorter;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteDefinition;

class SortByPath extends RouteDefinitionListSorter
{
    #[\Override]
    public function __invoke(RouteDefinition $a, RouteDefinition $b): int
    {
        return $this->sort_asc * ($a->getRoutePath() <=> $b->getRoutePath());
    }
}
