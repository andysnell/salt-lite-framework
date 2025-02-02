<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session;

use PhoneBurner\SaltLite\Framework\Http\Session\Session;

interface SessionFactory
{
    public function start(): Session;
}
