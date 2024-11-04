<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Symfony\Component\Messenger\MessageBus as SymfonyMessageBus;

#[Internal]
class SymfonyMessageBusAdapter extends SymfonyMessageBus implements MessageBus
{
}
