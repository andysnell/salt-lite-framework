<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Container;

use PhoneBurner\SaltLite\Framework\Container\ObjectContainer\MutableObjectContainer;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

/**
 * @extends MutableObjectContainer<ReceiverInterface>
 */
class ReceiverContainer extends MutableObjectContainer
{
}
