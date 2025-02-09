<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

class Transport
{
    public const ASYNC = 'async';
    public const SYNC = 'sync';
    public const FAILED = 'failed';
}
