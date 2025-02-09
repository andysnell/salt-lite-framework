<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class InvalidServiceProvider extends \LogicException implements ContainerExceptionInterface
{
    public function __construct(string $class)
    {
        parent::__construct($class . ' is not a valid service provider.');
    }
}
