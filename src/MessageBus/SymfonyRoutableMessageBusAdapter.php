<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use Symfony\Component\Messenger\RoutableMessageBus as SymfonyRoutableMessageBus;

#[Internal]
class SymfonyRoutableMessageBusAdapter extends SymfonyRoutableMessageBus implements MessageBus
{
}
