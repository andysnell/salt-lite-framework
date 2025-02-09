<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use Psr\Log\LoggerAwareInterface;

interface ServiceContainer extends MutableContainer, InvokingContainer, LoggerAwareInterface
{
}
