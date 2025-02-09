<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class CircularDependency extends \LogicException implements ContainerExceptionInterface
{
    public function __construct(string|null $top_level_id, string $dependency_id)
    {
        parent::__construct(
            \sprintf('Circular dependency detected for "%s" while resolving "%s".', $top_level_id, $dependency_id),
        );
    }
}
