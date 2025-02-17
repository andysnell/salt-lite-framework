<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session\Exception;

class SessionNotStarted extends \LogicException implements HttpSessionException
{
}
