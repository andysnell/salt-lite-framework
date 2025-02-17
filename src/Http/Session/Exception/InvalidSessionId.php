<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Exception;

class InvalidSessionId extends \UnexpectedValueException implements HttpSessionException
{
}
