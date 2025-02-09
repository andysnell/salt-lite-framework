<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Exception;

use Psr\Container\ContainerExceptionInterface;

class ResolutionFailure extends \LogicException implements ContainerExceptionInterface
{
}
