<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Attribute\Usage\Internal;
use PhoneBurner\SaltLite\MessageBus\MessageBus;
use Symfony\Component\Messenger\RoutableMessageBus as SymfonyRoutableMessageBus;

#[Internal]
class SymfonyRoutableMessageBusAdapter extends SymfonyRoutableMessageBus implements MessageBus
{
}
