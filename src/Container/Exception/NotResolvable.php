<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class NotResolvable extends \LogicException implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        parent::__construct($class . ' Must Be Set Explicitly in the Container');
    }
}
