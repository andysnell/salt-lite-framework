<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Container;

use PhoneBurner\SaltLite\Container\ObjectContainer\MutableObjectContainer;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;

/**
 * @extends MutableObjectContainer<SenderInterface>
 */
class SenderContainer extends MutableObjectContainer
{
}
