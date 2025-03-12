<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

class Transport
{
    final public const string ASYNC = 'async';
    final public const string SYNC = 'sync';
    final public const string FAILED = 'failed';
}
