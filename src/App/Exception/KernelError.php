<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\Exception;

use LogicException;

class KernelError extends LogicException implements BootError
{
}
