<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFound extends \LogicException implements NotFoundExceptionInterface
{
}
