<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class UnableToAutoResolveParameter extends \LogicException implements ContainerExceptionInterface
{
    public function __construct(\ReflectionParameter $parameter)
    {
        parent::__construct(\vsprintf("Unable to resolve value for parameter \$%s for class %s", [
            $parameter->getName(),
            $parameter->getDeclaringClass()?->getName(),
        ]));
    }
}
