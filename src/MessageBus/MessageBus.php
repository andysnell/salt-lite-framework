<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface MessageBus
{
    public function dispatch(object $message): object;
}
